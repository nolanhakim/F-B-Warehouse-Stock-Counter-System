<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Mutation;
use App\Models\OpnameSession;
use Illuminate\Http\Request;

class OpnameController extends Controller
{
    public function index()
    {
        $role = session('role', 'staff');
        $view = in_array($role, ['super', 'admin', 'staff']) ? $role . '.opname' : 'staff.opname';

        return view($view, [
            'active' => 'opname',
            'pageTitle' => 'Sesi Stock Opname',
            'items' => $this->stock(),
            'session' => OpnameSession::where('status', 'active')->latest()->first(),
        ]);
    }

    public function data()
    {
        return response()->json(['items' => $this->stock()]);
    }

    public function start(Request $request)
    {
        $active = OpnameSession::where('status', 'active')->exists();

        if ($active) {
            return back()->withErrors(['opname' => 'Masih ada sesi opname yang berlangsung. Selesaikan atau batalkan dulu.']);
        }

        OpnameSession::create([
            'status' => 'active',
            'started_by' => session('name'),
            'started_at' => now(),
        ]);

        ActivityLog::record(session('email'), 'Opname', null, 'Mulai sesi stock opname');

        return back()->with('success', 'Sesi opname dimulai. Mulai hitung fisik barang.');
    }

    public function draft(Request $request)
    {
        $session = OpnameSession::where('status', 'active')->latest()->firstOrFail();
        $counts = $request->input('counts', []);

        $session->update([
            'counts' => array_map(fn ($v) => (int) $v, $counts),
        ]);

        return back()->with('success', 'Kemajuan opname disimpan.');
    }

    public function finish(Request $request)
    {
        $session = OpnameSession::where('status', 'active')->latest()->firstOrFail();
        $counts = $request->input('counts', []);
        $diffCount = 0;
        $changed = [];

        foreach ($this->stock() as $s) {
            $physical = isset($counts[$s['sku']]) && $counts[$s['sku']] !== '' ? (int) $counts[$s['sku']] : $s['stock'];
            $diff = $physical - $s['stock'];
            if ($diff === 0) continue;

            $number = 'ADJ' . '/' . now()->format('dmy') . '-' . str_pad(Mutation::where('type', 'Opname')->count() + 1, 3, '0', STR_PAD_LEFT);

            Mutation::create([
                'document_number' => $number,
                'type' => 'Opname',
                'item_id' => Item::where('sku', $s['sku'])->value('id'),
                'batch' => null,
                'expired_at' => null,
                'quantity' => $diff,
                'rack' => $s['rack'],
                'pic' => session('name'),
                'status' => 'approved',
            ]);

            $changed[] = $s['sku'] . ' ' . ($diff > 0 ? '+' : '') . $diff;
            $diffCount++;
        }

        ActivityLog::record(session('email'), 'Opname', null, 'Selesai opname: ' . $diffCount . ' selisih (' . implode(', ', $changed) . ')');

        $session->update([
            'status' => 'completed',
            'counts' => array_map(fn ($v) => (int) $v, $counts),
            'finished_at' => now(),
        ]);

        return back()->with('success', 'Opname selesai. ' . $diffCount . ' selisih disimpan sebagai mutasi Opname.');
    }

    public function cancel()
    {
        OpnameSession::where('status', 'active')->latest()->firstOrFail()->delete();

        ActivityLog::record(session('email'), 'Opname', null, 'Batalkan sesi stock opname');

        return back()->with('success', 'Sesi opname dibatalkan.');
    }

    private function stock()
    {
        return Item::where('is_active', true)
            ->withSum(['mutations' => fn ($q) => $q->where('status', 'approved')], 'quantity')
            ->orderBy('name')
            ->get()
            ->map(fn ($i) => [
                'sku' => $i->sku,
                'name' => $i->name,
                'category' => $i->category,
                'unit_count' => $i->unit_count,
                'unit_buy' => $i->unit_buy,
                'rack' => $i->rack,
                'safety_stock' => (int) $i->safety_stock,
                'stock' => (int) $i->mutations_sum_quantity,
            ])
            ->values();
    }
}