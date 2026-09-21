<?php

namespace App\Http\Controllers;

use App\Models\Mutation;

class AlertsController extends Controller
{
    public function index()
    {
        $rows = Mutation::with('item')
            ->where('status', 'approved')
            ->whereNotNull('expired_at')
            ->get()
            ->groupBy(fn ($m) => $m->item_id . '|' . $m->batch . '|' . $m->expired_at->format('Y-m-d'))
            ->filter(fn ($g) => $g->sum('quantity') > 0)
            ->map(function ($g) {
                $m = $g->first();
                $daysLeft = now()->startOfDay()->diffInDays($m->expired_at->startOfDay(), false);

                $level = $daysLeft <= 0 ? 'Danger' : ($daysLeft <= 7 ? 'H7' : 'H30');

                return [
                    'level' => $level,
                    'sku' => $m->item->sku,
                    'name' => $m->item->name,
                    'batch' => $m->batch,
                    'rack' => $m->item->rack,
                    'qty' => $g->sum('quantity'),
                    'expired_at' => $m->expired_at->format('Y-m-d'),
                    'days' => $daysLeft,
                    'recom' => $level === 'Danger' ? 'Segera alokasi / waste' : ($level === 'H7' ? 'Prioritas FEFO — alokasi keluar' : 'Pantau jelang H-7'),
                ];
            })
            ->values();

        $role = session('role', 'staff');
        $view = in_array($role, ['super', 'admin', 'staff']) ? $role . '.alerts' : 'staff.alerts';

        return view($view, [
            'active' => 'alerts',
            'pageTitle' => 'Alert & Kedaluwarsa',
            'rows' => $rows,
            'counts' => [
                'danger' => $rows->where('level', 'Danger')->count(),
                'h7' => $rows->where('level', 'H7')->count(),
                'h30' => $rows->where('level', 'H30')->count(),
            ],
        ]);
    }
}