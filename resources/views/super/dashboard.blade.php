@extends('layouts.app')

@section('content')
@php
$stats = [
    ['label'=>'Total Pengguna', 'value'=>$usersTotal, 'delta'=>$usersActive.' aktif dari '.$usersTotal, 'icon'=>'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z','ok'],
    ['label'=>'Log Aktivitas (hari ini)', 'value'=>$logsToday, 'delta'=>''.$mutasiMonth.' mutasi bulan ini', 'icon'=>'M4 6h2v2H4V6zm0 5h2v2H4v-2zm0 5h2v2H4v-2zm16-8H10v2h10V8zm0 5H10v2h10v-2zm0 5H10v2h10v-2z','ok'],
    ['label'=>'Total Barang', 'value'=>$itemsTotal, 'delta'=>$itemsActive.' aktif · '.$itemCategories.' kategori · '.$itemRacks.' rak', 'icon'=>'M12 2l1 5h6l-4 6 1 5-4-3-4 3 1-5-4-6h6l1-5z','ok'],
    ['label'=>'Alert Expired', 'value'=>$alertTotal, 'delta'=>$alertH7.' H-7 · '.$alertDanger.' EXPIRED', 'danger'=>true, 'icon'=>'M10 20h4a2 2 0 0 1-4 0zm8-6v-4a6 6 0 0 0-12 0v4l-2 2v1h16v-1l-2-2z'],
];
@endphp
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Dashboard Super Admin</h1>
    <div class="flex items-center gap-2">
        <span class="text-sm text-slate-500">{{ $todayStr }}</span>
    </div>
</div>

{{-- STAT --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @foreach($stats as $s)
    <div class="bg-white border border-slate-200 rounded-lg p-4 status">
        <div class="flex items-center justify-between">
            <div class="label-caps text-slate-500">{{ $s['label'] }}</div>
            <svg class="w-5 h-5 {{ isset($s['danger']) ? 'text-red-600' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $s['icon'] }}"/></svg>
        </div>
        <div data-pop class="mt-2 font-mono text-2xl font-bold tabular">{{ $s['value'] }}</div>
        <div class="mt-1 text-xs {{ isset($s['danger']) ? 'text-red-600' : 'text-green-600' }}">{{ $s['delta'] }}</div>
    </div>
    @endforeach
</div>

{{-- USER MATRIX + LOG FEED --}}
<div class="grid lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Pengguna Terdaftar</h2>
            <a href="/users" class="text-sm text-brand hover:underline">Kelola semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-2 font-medium">User</th>
                        <th class="px-3 py-2 text-center font-medium">Role</th>
                        <th class="px-3 py-2 text-center font-medium">Status</th>
                        <th class="px-5 py-2 text-center font-medium">Terakhir Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                    @php
                        $parts = explode(' ', $u->name);
                        $ini = strtoupper(mb_substr($parts[0] ?? '',0,1)).strtoupper(mb_substr($parts[1] ?? '',0,1));
                        $lastLogin = $u->last_login_at?->diffForHumans() ?? 'Belum pernah';
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-brand/10 text-brand flex items-center justify-center text-xs font-bold">{{ $ini }}</span>
                                <span class="font-medium">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold
                            {{ $u->role=='super' ? 'bg-red-50 border border-red-300 text-red-600' : ($u->role=='admin' ? 'bg-slate-100 border border-slate-300 text-slate-700' : 'bg-blue-50 border border-blue-300 text-brand') }}">{{ role_name($u->role) }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-center"><span class="inline-flex items-center gap-1 text-[10px] font-semibold {{ $u->is_active ? 'text-green-600' : 'text-slate-400' }}"><span class="w-1.5 h-1.5 rounded-full {{ $u->is_active ? 'bg-green-500' : 'bg-slate-300' }}"></span>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-5 py-2.5 text-center font-mono text-xs text-slate-500">{{ $lastLogin }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Aktivitas Terbaru</h2>
            <a href="/logs" class="text-sm text-brand hover:underline">Semua log →</a>
        </div>
        <div class="divide-y divide-slate-100">
            @php
                $color = ['Create'=>'bg-green-500','Inbound'=>'bg-green-500','Approval'=>'bg-purple-500','Outbound'=>'bg-blue-500','Waste'=>'bg-red-500','Update'=>'bg-amber-500','Reject'=>'bg-red-500','Login'=>'bg-slate-400','Logout'=>'bg-slate-400','Delete'=>'bg-red-500'];
                $module = ['Inbound'=>'Mutasi','Outbound'=>'Mutasi','Waste'=>'Waste','Approval'=>'Waste','Reject'=>'Waste','Create'=>'Mutasi','Update'=>'Master','Login'=>'Auth','Logout'=>'Auth'];
            @endphp
            @forelse($logsLatest as $a)
            <div class="flex items-start gap-3 px-5 py-3">
                <span class="mt-1.5 w-2 h-2 rounded-full {{ $color[$a->action] ?? 'bg-slate-400' }} shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm"><b>{{ $a->email ?? 'Sistem' }}</b> <span class="text-slate-500">melakukan</span> <b class="text-slate-700">{{ $a->action }}</b> <span class="text-slate-500">pada</span> <span class="font-mono text-xs">{{ $a->object ?? '-' }}</span></div>
                    <div class="text-xs text-slate-400 mt-0.5 font-mono tabular">{{ $a->created_at->format('H:i') }} WIB</div>
                </div>
                <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold bg-slate-50 border-slate-200 text-slate-500">{{ $module[$a->action] ?? 'Sistem' }}</span>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada aktivitas tercatat</div>
            @endforelse
        </div>
    </div>
</div>

{{-- SISTEM OVERVIEW --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200"><h2 class="font-semibold text-lg">Ringkasan Modul Sistem</h2></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5">
        @php
        $mods = [
            ['Master Barang', $itemsTotal.' SKU · '.$itemCategories.' kategori · '.$itemRacks.' rak', '/master'],
            ['Mutasi Stok', $inboundToday.' inbound · '.$outboundToday.' outbound hari ini', '/mutasi'],
            ['Stock Opname', $itemsActive.' barang terdaftar sebagai target hitung', '/opname'],
            ['Kartu Stok & Audit', $mutasiMonth.' mutasi tercatat bulan ini', '/kartu-stok'],
            ['Waste & Approval', $pendingWaste.' menunggu persetujuan', '/waste'],
            ['Alert Expired', $alertTotal.' batch butuh aksi', '/alerts'],
            ['Manajemen User', $usersTotal.' akun · '.$usersActive.' aktif', '/users'],
            ['Log Aktivitas', $logsToday.' aktivitas hari ini', '/logs'],
        ];
        @endphp
        @foreach($mods as $m)
        <a href="{{ $m[2] }}" class="rounded-lg border border-slate-200 p-4 hover:border-brand hover:shadow-sm hover-lift transition">
            <div class="font-semibold">{{ $m[0] }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $m[1] }}</div>
            <div class="text-brand text-sm mt-2 font-medium">Buka →</div>
        </a>
        @endforeach
    </div>
</div>
@endsection