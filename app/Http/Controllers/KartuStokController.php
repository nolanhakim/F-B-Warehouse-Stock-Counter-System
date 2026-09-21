<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class KartuStokController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with('mutations')->orderBy('sku')->get();

        $item = $request->input('sku')
            ? Item::where('sku', $request->input('sku'))->first()
            : $items->first();

        $rows = collect();
        if ($item) {
$chrono = $item->mutations()->where('status', 'approved')->orderBy('id')->get();
            $balance = 0;
            $withBal = $chrono->map(function ($m) use (&$balance) {
                $balance += $m->quantity;
                $m->balance = $balance;
                return $m;
            });
            $rows = $withBal->reverse()->values();
        }

        $role = session('role', 'staff');
        $view = in_array($role, ['super', 'admin', 'staff']) ? $role . '.kartu-stok' : 'staff.kartu-stok';

        return view($view, [
            'active' => 'kartu-stok',
            'pageTitle' => 'Kartu Stok & Audit Trail',
            'items' => $items,
            'item' => $item,
            'rows' => $rows,
            'logs' => ActivityLog::latest()->limit(8)->get(),
        ]);
    }

    public function export(Request $request)
    {
        $item = Item::where('sku', $request->input('sku'))->firstOrFail();

        $chrono = $item->mutations()->where('status', 'approved')->orderBy('id')->get();
        $balance = 0;
        $rows = $chrono->map(function ($m) use (&$balance) {
            $balance += $m->quantity;
            return [$m, $balance];
        });

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['KARTU STOK', $item->sku, $item->name]);
        fputcsv($out, ['Kategori', $item->category, 'Lokasi', $item->rack ?? '-']);
        fputcsv($out, ['Satuan Hitung', $item->unit_count ?? '-', 'Safety Stock', $item->safety_stock]);
        fputcsv($out, []);
        fputcsv($out, ['Tanggal', 'No. Dokumen', 'Jenis', 'Masuk (+)', 'Keluar (-)', 'Saldo', 'Batch', 'Expired', 'User']);
        foreach ($rows as [$m, $bal]) {
            fputcsv($out, [
                $m->created_at->format('Y-m-d H:i'),
                $m->document_number,
                $m->type,
                $m->quantity > 0 ? $m->quantity : '',
                $m->quantity < 0 ? abs($m->quantity) : '',
                $bal,
                $m->batch ?? '',
                $m->expired_at?->format('Y-m-d') ?? '',
                $m->pic ?? '',
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kartu-stok-' . $item->sku . '-' . now()->format('Ymd') . '.csv"',
        ]);
    }
}