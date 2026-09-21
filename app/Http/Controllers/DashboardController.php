<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Mutation;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('role', 'staff');
        $view = match ($role) {
            'super' => 'super.dashboard',
            'admin' => 'admin.dashboard',
            default => 'staff.dashboard',
        };

        $data = ['active' => 'dashboard', 'pageTitle' => 'Dashboard'];

        $today = now();

        if ($role === 'super') {
            $users = User::orderByDesc('is_active')->orderBy('name')->get();

            $alertRows = $this->alertRows();

            $data += [
                'users' => $users,
                'todayStr' => $this->indonesianDate($today),
                'usersTotal' => $users->count(),
                'usersActive' => $users->where('is_active', true)->count(),
                'logsToday' => ActivityLog::today()->count(),
                'logsLatest' => ActivityLog::latest()->limit(6)->get(),
                'itemsTotal' => Item::count(),
                'itemsActive' => Item::where('is_active', true)->count(),
                'itemCategories' => Item::distinct('category')->count('category'),
                'itemRacks' => Item::whereNotNull('rack')->distinct('rack')->count('rack'),
                'inboundToday' => Mutation::where('type', 'Inbound')->where('status', 'approved')->whereDate('created_at', $today)->count(),
                'outboundToday' => Mutation::where('type', 'Outbound')->where('status', 'approved')->whereDate('created_at', $today)->count(),
                'wasteToday' => Mutation::where('type', 'Waste')->whereDate('created_at', $today)->count(),
                'pendingWaste' => Mutation::where('type', 'Waste')->where('status', 'pending')->count(),
                'mutasiMonth' => Mutation::whereBetween('created_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])->count(),
                'alertRows' => $alertRows,
                'alertDanger' => $alertRows->where('level', 'Danger')->count(),
                'alertH7' => $alertRows->where('level', 'H7')->count(),
                'alertTotal' => $alertRows->count(),
            ];
        }

        if ($role === 'admin' || $role === 'staff') {
            $alertRows = $this->alertRows();
            $lowStockItems = Item::withSum(['mutations' => fn ($q) => $q->where('status', 'approved')], 'quantity')
                ->where('is_active', true)
                ->orderBy('safety_stock')
                ->get()
                ->filter(fn ($i) => ($i->mutations_sum_quantity ?? 0) < $i->safety_stock)
                ->take(5)
                ->values();

            $data += [
                'todayStr' => $this->indonesianDate($today),
                'itemsTotal' => Item::where('is_active', true)->count(),
                'inboundToday' => Mutation::where('type', 'Inbound')->where('status', 'approved')->whereDate('created_at', $today)->count(),
                'outboundToday' => Mutation::where('type', 'Outbound')->where('status', 'approved')->whereDate('created_at', $today)->count(),
                'todayMutations' => Mutation::with('item')
                    ->whereIn('type', ['Inbound', 'Outbound'])
                    ->where('status', 'approved')
                    ->whereDate('created_at', $today)
                    ->latest()
                    ->limit(5)
                    ->get(),
                'pendingWaste' => Mutation::where('type', 'Waste')->where('status', 'pending')->count(),
                'alertRows' => $alertRows,
                'alertDanger' => $alertRows->where('level', 'Danger')->count(),
                'alertH7' => $alertRows->where('level', 'H7')->count(),
                'alertTotal' => $alertRows->count(),
                'lowStockItems' => $lowStockItems,
                'chart' => collect(range(5, 0))->map(function ($i) {
                    $d = now()->subMonths($i);
                    $q = Mutation::where('status', 'approved')->whereBetween('created_at', [$d->startOfMonth(), $d->endOfMonth()]);
                    return [
                        'label' => $d->format('M y'),
                        'Inbound' => (clone $q)->where('type', 'Inbound')->count(),
                        'Outbound' => (clone $q)->where('type', 'Outbound')->count(),
                        'Waste' => (clone $q)->where('type', 'Waste')->count(),
                        'Opname' => (clone $q)->where('type', 'Opname')->count(),
                    ];
                })->values(),
            ];
        }

        return view($view, $data);
    }

    private function indonesianDate(\Illuminate\Support\Carbon $d): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return $days[$d->dayOfWeek] . ', ' . $d->day . ' ' . $months[$d->month] . ' ' . $d->year;
    }

    private function alertRows(): Collection
    {
        $today = now();
        return Mutation::with('item')
            ->where('status', 'approved')
            ->whereNotNull('expired_at')
            ->get()
            ->groupBy(fn ($m) => $m->item_id . '|' . $m->batch . '|' . $m->expired_at->format('Y-m-d'))
            ->filter(fn ($g) => $g->sum('quantity') > 0)
            ->map(function ($g) use ($today) {
                $m = $g->first();
                $daysLeft = $today->startOfDay()->diffInDays($m->expired_at->startOfDay(), false);
                return [
                    'level' => $daysLeft <= 0 ? 'Danger' : ($daysLeft <= 7 ? 'H7' : 'H30'),
                    'sku' => $m->item->sku,
                    'name' => $m->item->name,
                    'batch' => $m->batch,
                    'rack' => $m->item->rack,
                    'expired_at' => $m->expired_at->format('Y-m-d'),
                    'days' => $daysLeft,
                ];
            })
            ->values();
    }
}