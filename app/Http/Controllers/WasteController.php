<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Mutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        $role = session('role', 'staff');

        $isStaff = $role === 'staff';
        $pending = Mutation::with('item')->where('type', 'Waste')->where('status', 'pending')->latest()->get();

        $query = Mutation::with('item')->where('type', 'Waste');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        $history = $query->latest()->paginate(10)->withQueryString();

        if ($isStaff) {
            $pending = $pending->where('pic', session('name'))->values();
            $history->setCollection($history->getCollection()->filter(fn ($m) => $m->pic === session('name'))->values());
        }

        $view = in_array($role, ['super', 'admin']) ? $role . '.waste' : 'staff.waste';

        return view($view, [
            'active' => 'waste',
            'pageTitle' => 'Waste & Persetujuan',
            'pending' => $pending,
            'history' => $history,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
            'canApprove' => !$isStaff,
        ]);
    }

    public function approve(Mutation $mutation): RedirectResponse
    {
        $this->authorizeApprover();
        $mutation->update(['status' => 'approved']);
        ActivityLog::record(session('email'), 'Approval', $mutation->document_number, 'Setujui waste: ' . $mutation->document_number);

        return redirect()->route('waste')->with('success', 'Waste ' . $mutation->document_number . ' disetujui.');
    }

    public function reject(Mutation $mutation): RedirectResponse
    {
        $this->authorizeApprover();
        $mutation->update(['status' => 'rejected']);
        ActivityLog::record(session('email'), 'Reject', $mutation->document_number, 'Tolak waste: ' . $mutation->document_number);

        return redirect()->route('waste')->with('success', 'Waste ' . $mutation->document_number . ' ditolak.');
    }

    private function authorizeApprover(): void
    {
        abort_unless(in_array(session('role'), ['admin', 'super']), 403);
    }

    public function export(Request $request)
    {
        $query = Mutation::with('item')->where('type', 'Waste');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $rows = $query->orderBy('id')->get();

        if (session('role') === 'staff') {
            $rows = $rows->where('pic', session('name'))->values();
        }

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tanggal', 'No. Dokumen', 'SKU', 'Barang', 'Qty', 'Batch', 'PIC', 'Status']);
        foreach ($rows as $m) {
            fputcsv($out, [
                $m->created_at->format('Y-m-d H:i'),
                $m->document_number,
                $m->item->sku ?? '',
                $m->item->name ?? '',
                $m->quantity,
                $m->batch ?? '',
                $m->pic ?? '',
                $m->status,
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="waste-' . now()->format('Ymd') . '.csv"',
        ]);
    }
}