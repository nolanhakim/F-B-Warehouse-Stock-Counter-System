@extends('layouts.app')

@section('content')
@php
$stats = [
    ['label'=>'Total SKU', 'value'=>$itemsTotal, 'delta'=>'barang aktif', 'icon'=>'M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z'],
    ['label'=>'Mutasi Hari Ini', 'value'=>$inboundToday + $outboundToday, 'delta'=>'In '.$inboundToday.' · Out '.$outboundToday, 'icon'=>'M12 2l1 5h6l-4 6 1 5-4-3-4 3 1-5-4-6h6l1-5z'],
    ['label'=>'Alert Expired', 'value'=>$alertTotal, 'delta'=>$alertDanger.' expired · '.$alertH7.' H-7', 'danger'=>$alertTotal > 0, 'icon'=>'M10 20h4a2 2 0 0 1-4 0zm8-6v-4a6 6 0 0 0-12 0v4l-2 2v1h16v-1l-2-2z'],
    ['label'=>'Low Stock', 'value'=>$lowStockItems->count(), 'delta'=>'di bawah safety stock', 'warn'=>true, 'icon'=>'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
];
@endphp

{{-- PAGE TITLE --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Dashboard Ringkasan Gudang</h1>
    <div class="text-sm text-slate-500">{{ $todayStr }} — Gudang Pusat</div>
</div>

@if($pendingWaste > 0)
<a href="{{ route('waste') }}" class="flex items-center gap-3 mb-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 hover:bg-amber-100 transition">
    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
    <span class="font-semibold">{{ $pendingWaste }} waste menunggu persetujuan.</span>
    <span class="text-xs underline ml-auto shrink-0">Tinjau sekarang →</span>
</a>
@endif

{{-- STAT CARDS --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @foreach($stats as $s)
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="label-caps text-slate-500">{{ $s['label'] }}</div>
            <svg class="w-5 h-5 {{ isset($s['danger']) ? 'text-red-600' : (isset($s['warn']) ? 'text-amber-600' : 'text-slate-400') }}" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $s['icon'] }}"/></svg>
        </div>
        <div data-pop class="mt-2 font-mono text-2xl font-bold tabular">{{ $s['value'] }}</div>
        <div class="mt-1 text-xs {{ isset($s['danger']) ? 'text-red-600' : (isset($s['warn']) ? 'text-amber-600' : 'text-green-600') }}">{{ $s['delta'] }}</div>
    </div>
    @endforeach
</div>

{{-- CHART MUTASI --}}
@php
$maxBar = max(1, collect($chart)->map(fn ($c) => $c['Inbound'] + $c['Outbound'] + $c['Waste'] + $c['Opname'])->max());
$types = ['Inbound' => 'bg-green-600', 'Outbound' => 'bg-blue-600', 'Waste' => 'bg-red-600', 'Opname' => 'bg-slate-400'];
@endphp
<div class="bg-white border border-slate-200 rounded-lg p-5 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <h2 class="font-semibold text-lg">Tren Mutasi — 6 Bulan Terakhir</h2>
        <div class="flex flex-wrap gap-3 text-xs text-slate-500">
            @foreach($types as $t => $c)
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm {{ $c }}"></span>{{ $t }}</span>
            @endforeach
        </div>
    </div>
    <div class="flex items-end gap-2 h-44">
        @foreach($chart as $c)
        @php
        $total = $c['Inbound'] + $c['Outbound'] + $c['Waste'] + $c['Opname'];
        @endphp
        <div class="flex-1 flex flex-col items-center justify-end gap-1 min-w-0">
            <div class="w-full flex flex-col justify-end rounded-t-md overflow-hidden" style="height: {{ max(2, round(($total / $maxBar) * 100)) }}%">
                @foreach($types as $t => $tc)
                @if($c[$t] > 0)
                <div class="{{ $tc }}" style="height: {{ ($c[$t] / max(1, $total)) * 100 }}%" title="{{ $t }}: {{ $c[$t] }}"></div>
                @endif
                @endforeach
            </div>
            <div class="text-center w-full border-t border-slate-200 pt-1">
                <div class="text-[10px] text-slate-400 label-caps">{{ $c['label'] }}</div>
                <div class="font-mono text-xs font-semibold tabular">{{ $total }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ALERT EXPIRED --}}
<div class="bg-white border border-slate-200 rounded-lg mb-5">
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
        <h2 class="font-semibold text-lg">Peringatan Kedaluwarsa (FEFO)</h2>
        <a href="/alerts" class="text-sm text-brand hover:underline">Lihat semua →</a>
    </div>
    <div class="grid md:grid-cols-2 gap-3 p-5">
        @forelse($alertRows as $e)
        <div class="flex items-center justify-between rounded-md px-3 py-2.5
            {{ $e['level']=='Danger' ? 'bg-[#FEF2F2] border border-red-200 text-red-700' : 'bg-[#FFFBEB] border border-amber-200 text-amber-700' }}">
            <div class="flex items-center gap-3 min-w-0">
                <span class="font-mono text-xs text-slate-500">{{ $e['sku'] }}</span>
                <div class="min-w-0">
                    <div class="font-medium truncate">{{ $e['name'] }}</div>
                    <div class="font-mono text-[10px] text-slate-500">{{ $e['batch'] }} · {{ $e['rack'] }}</div>
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="font-mono text-xs tabular">{{ $e['expired_at'] }}</div>
                <div class="label-caps mt-0.5">{{ $e['level']=='Danger' ? '● EXPIRED' : ($e['days']<=7 ? '● Exp '.$e['days'].' hari' : '● H-30') }}</div>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center text-sm text-slate-500 py-6">Tidak ada barang mendekati kedaluwarsa.</div>
        @endforelse
    </div>
</div>

{{-- TABLES: LOW STOCK + RECENT MUTATION --}}
<div class="grid lg:grid-cols-2 gap-5">
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Low Stock (di bawah safety stock)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-2 font-medium">SKU</th>
                        <th class="px-3 py-2 font-medium">Nama</th>
                        <th class="px-3 py-2 text-right font-medium">Stok</th>
                        <th class="px-3 py-2 text-right font-medium">Min.</th>
                        <th class="px-5 py-2 text-center font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lowStockItems as $i)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $i->sku }}</td>
                        <td class="px-3 py-2 font-medium">{{ $i->name }}</td>
                        <td class="px-3 py-2 font-mono text-right tabular font-bold text-red-600">{{ $i->mutations_sum_quantity ?? 0 }}</td>
                        <td class="px-3 py-2 font-mono text-right tabular text-slate-500">{{ $i->safety_stock }}</td>
                        <td class="px-5 py-2 text-center"><span class="inline-flex items-center gap-1 rounded bg-[#FFFBEB] border border-amber-300 text-amber-700 text-[10px] font-semibold px-2 py-0.5">● Low Stock</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-6 text-center text-sm text-slate-500">Semua stok di atas safety stock.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-lg">Mutasi Terbaru</h2>
            <a href="/mutasi" class="text-sm text-brand hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50">
                        <th class="px-5 py-2 font-medium">Dokumen</th>
                        <th class="px-3 py-2 font-medium">Jenis</th>
                        <th class="px-3 py-2 font-medium">SKU</th>
                        <th class="px-3 py-2 text-right font-medium">Qty</th>
                        <th class="px-3 py-2 text-center font-medium">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayMutations as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-2 font-mono text-xs text-slate-500">{{ $m->document_number }}</td>
                        <td class="px-3 py-2">
                            @php $c = ['Inbound'=>'bg-green-50 text-green-700 border-green-300','Outbound'=>'bg-blue-50 text-brand border-blue-300','Waste'=>'bg-red-50 text-red-600 border-red-300','Opname'=>'bg-slate-100 text-slate-600 border-slate-300'][$m->type] ?? 'bg-slate-100 text-slate-600 border-slate-300'; @endphp
                            <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ $c }}">{{ $m->type }}</span>
                        </td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $m->item->sku }}</td>
                        <td class="px-3 py-2 font-mono text-right tabular font-semibold {{ $m->type=='Inbound' ? 'text-green-600' : 'text-red-600' }}">{{ $m->type=='Inbound' ? '+' : '-' }}{{ $m->quantity }}</td>
                        <td class="px-3 py-2 text-center font-mono text-xs text-slate-500 tabular">{{ $m->created_at->format('H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-6 text-center text-sm text-slate-500">Belum ada mutasi hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection