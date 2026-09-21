<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class LogController extends Controller
{
    public function index()
    {
        abort_unless(session('role') === 'super', 403);

        $logs = ActivityLog::latest()->paginate(10)->withQueryString();
        $all = ActivityLog::query();

        $stats = [
            'total' => (clone $all)->count(),
            'today' => (clone $all)->whereDate('created_at', today())->count(),
            'login_today' => (clone $all)->today()->where('action', 'Login')->count(),
            'perubahan' => (clone $all)->whereIn('action', ['Create', 'Update', 'Delete'])->count(),
        ];

        return view('super.logs', [
            'active' => 'logs',
            'pageTitle' => 'Log Aktivitas Sistem',
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }
}
