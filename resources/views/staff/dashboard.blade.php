@extends('layouts.app')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Dashboard Checker</h1>
    <div class="text-sm text-slate-500">{{ $todayStr }} — Gudang Pusat</div>
</div>

{{-- STAT RINGKASAN --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="label-caps text-slate-500 text-xs mb-1">Inbound Hari Ini</div>
        <div class="font-mono text-2xl font-bold tabular text-green-600">{{ $inboundToday }}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="label-caps text-slate-500 text-xs mb-1">Outbound Hari Ini</div>
        <div class="font-mono text-2xl font-bold tabular text-blue-600">{{ $outboundToday }}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="label-caps text-slate-500 text-xs mb-1">Waste Menunggu</div>
        <div class="font-mono text-2xl font-bold tabular text-amber-600">{{ $pendingWaste }}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="label-caps text-slate-500 text-xs mb-1">Alert Kadaluarsa</div>
        <div class="font-mono text-2xl font-bold tabular text-red-600">{{ $alertRows->where('level', 'Danger')->count() }}</div>
        <div class="text-xs text-slate-500 mt-0.5">{{ $alertRows->where('level', 'H7')->count() }} dalam 7 hari</div>
    </div>
</div>

{{-- TUGAS HARI INI + PERHATIAN --}}
<div class="grid lg:grid-cols-2 gap-5">
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Tugas Mutasi Hari Ini</h2>
            <a href="{{ route('mutasi') }}" class="text-sm text-brand hover:underline">Lihat semua →</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($todayMutations as $m)
            <div class="flex items-center gap-3 px-5 py-3">
                <span class="w-2 h-2 rounded-full {{ $m->quantity > 0 ? 'bg-green-500' : 'bg-red-500' }} shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <div class="font-mono text-xs text-slate-500">{{ $m->document_number }}</div>
                    <div class="font-medium truncate">{{ $m->item->name ?? '-' }}</div>
                </div>
                <div class="font-mono tabular font-semibold {{ $m->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</div>
                <div class="text-right shrink-0">
                    @php
                        $typeColors = [
                            'Inbound' => 'bg-green-50 text-green-700 border-green-300',
                            'Outbound' => 'bg-blue-50 text-brand border-blue-300',
                            'Waste' => 'bg-red-50 text-red-600 border-red-300',
                            'Opname' => 'bg-slate-100 text-slate-600 border-slate-300',
                        ];
                        $tc = $typeColors[$m->type] ?? 'bg-slate-100 text-slate-600 border-slate-300';
                    @endphp
                    <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ $tc }}">{{ $m->type }}</span>
                    <div class="text-[10px] text-slate-400 mt-1 font-mono tabular">{{ $m->created_at->format('H:i') }} WIB</div>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <p class="text-slate-500 font-medium">Belum ada mutasi hari ini</p>
                <p class="text-slate-400 text-xs mt-1">Catat inbound/outbound untuk melihat riwayat di sini.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Perhatian — Rak Tugas</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @if($alertRows->isNotEmpty())
            @foreach($alertRows as $a)
            <div class="flex items-start gap-3 px-5 py-3 {{ $a['level'] === 'Danger' ? 'bg-[#FEF2F2]' : 'bg-[#FFFBEB]' }}">
                <span class="w-2 h-2 rounded-full {{ $a['level'] === 'Danger' ? 'bg-red-600' : 'bg-amber-500' }} mt-1.5 shrink-0"></span>
                <div>
                    <div class="text-sm font-semibold {{ $a['level'] === 'Danger' ? 'text-red-700' : 'text-amber-700' }}">
                        {{ $a['level'] === 'Danger' ? '● EXPIRED — ' . abs($a['days']) . ' Hari' : '● H-' . $a['days'] }}
                    </div>
                    <div class="text-xs text-slate-600 mt-0.5">{{ $a['sku'] }} {{ $a['name'] }} · Batch {{ $a['batch'] }} · {{ $a['rack'] }}</div>
                </div>
            </div>
            @endforeach
            @endif
            @if($lowStockItems->isNotEmpty())
            @foreach($lowStockItems as $i)
            <div class="flex items-start gap-3 px-5 py-3 bg-[#FFFBEB]">
                <span class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                <div>
                    <div class="text-sm font-semibold text-amber-700">● Low Stock</div>
                    <div class="text-xs text-slate-600 mt-0.5">{{ $i->sku }} {{ $i->name }} · sisa {{ $i->mutations_sum_quantity ?? 0 }} dari min. {{ $i->safety_stock }}</div>
                </div>
            </div>
            @endforeach
            @endif
            @if($alertRows->isEmpty() && $lowStockItems->isEmpty())
            <div class="px-5 py-10 text-center">
                <p class="text-slate-500 font-medium">Tidak ada peringatan</p>
                <p class="text-slate-400 text-xs mt-1">Semua stok dalam kondisi baik.</p>
            </div>
            @endif
    </div>
</div>
@endsection
