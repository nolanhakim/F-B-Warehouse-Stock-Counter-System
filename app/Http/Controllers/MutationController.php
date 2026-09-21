<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $mutations = $this->indexQuery($request)->latest()->paginate(15)->withQueryString();

        $role = session('role', 'staff');
        $view = in_array($role, ['super', 'admin', 'staff']) ? $role . '.mutasi' : 'staff.mutasi';

        return view($view, [
            'active' => 'mutasi',
            'pageTitle' => 'Mutasi Stok',
            'mutations' => $mutations,
            'filters' => $request->only(['type', 'status', 'date_from', 'date_to']),
            'items' => Item::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:Inbound,Outbound,Waste',
            'item_id' => 'required|exists:items,id',
            'batch' => 'nullable|string|max:255',
            'expired_at' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
            'rack' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
        ]);

        $prefix = match ($data['type']) {
            'Inbound' => 'INB',
            'Outbound' => 'OUT',
            'Waste' => 'WST',
        };
        $last = Mutation::where('type', $data['type'])->count();
        $number = $prefix . '/' . now()->format('dmy') . '-' . str_pad($last + 1, 3, '0', STR_PAD_LEFT);

$item = Item::find($data['item_id']);
        $sign = $data['type'] === 'Inbound' ? 1 : -1;

        if (in_array($data['type'], ['Outbound', 'Waste'])) {
            $available = (int) Item::withSum(['mutations' => fn ($q) => $q->where('status', 'approved')], 'quantity')->find($data['item_id'])->mutations_sum_quantity;
            if ($available < $data['quantity']) {
                return back()->withErrors(['quantity' => 'Stok tidak cukup. Tersedia ' . $available . ' ' . ($item->unit_count ? '(' . $item->unit_count . ')' : 'unit') . '.'])->withInput();
            }
        }

        $mutation = Mutation::create([
            'document_number' => $number,
            'type' => $data['type'],
            'item_id' => $data['item_id'],
            'batch' => $data['batch'],
            'expired_at' => $data['expired_at'] ?? null,
            'quantity' => $sign * $data['quantity'],
            'rack' => $data['rack'],
            'pic' => $data['pic'] ?? session('name'),
            'status' => $data['type'] === 'Waste' ? 'pending' : 'approved',
        ]);

        ActivityLog::record(session('email'), $data['type'], $item->sku, $data['type'] . ': ' . $item->name . ' qty ' . $mutation->quantity . ($mutation->status === 'pending' ? ' (menunggu persetujuan)' : ''));

        return redirect()->route('mutasi')->with('success', $data['type'] . ' ' . $item->name . ' dicatat. No. ' . $number . ($mutation->status === 'pending' ? '. Menunggu persetujuan.' : ''));
    }

    public function export(Request $request)
    {
        $rows = $this->indexQuery($request)->orderBy('id')->get();

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tanggal', 'No. Dokumen', 'Jenis', 'SKU', 'Barang', 'Batch', 'Expired', 'Qty', 'Rak', 'PIC', 'Status']);
        foreach ($rows as $m) {
            fputcsv($out, [
                $m->created_at->format('Y-m-d H:i'),
                $m->document_number,
                $m->type,
                $m->item->sku ?? '',
                $m->item->name ?? '',
                $m->batch ?? '',
                $m->expired_at?->format('Y-m-d') ?? '',
                $m->quantity,
                $m->rack ?? '',
                $m->pic ?? '',
                $m->status,
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mutasi-' . now()->format('Ymd') . '.csv"',
        ]);
    }

    private function indexQuery(Request $request)
    {
        $query = Mutation::with('item');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $query;
    }
}
