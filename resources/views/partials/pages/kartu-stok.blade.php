@php
$cat = ['Dry Goods'=>'bg-slate-200 text-slate-700','Chilled'=>'bg-[#E0F2FE] text-[#0369A1]','Frozen'=>'bg-[#EDE9FE] text-[#6D28D9]','Packaging'=>'bg-[#FEF3C7] text-[#B45309]'];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Kartu Stok & Audit Trail</h1>
    <form method="GET" action="{{ route('kartu-stok') }}" class="flex gap-2 text-sm">
        <select name="sku" onchange="this.form.submit()" class="px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-brand focus:outline-none font-mono">
            @foreach($items as $it)
            <option value="{{ $it->sku }}" {{ $item && $item->sku === $it->sku ? 'selected' : '' }}>{{ $it->sku }} — {{ $it->name }}</option>
            @endforeach
        </select>
        <button class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-md">Terapkan</button>
    </form>
</div>

@if($item)
{{-- CARD HEADER --}}
<div class="bg-white border border-slate-200 rounded-lg p-5 mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="text-xl font-bold">{{ $item->sku }}</div>
            <div class="text-sm font-medium text-slate-600">{{ $item->name }}</div>
            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                <span>Kategori: <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold {{ $cat[$item->category] ?? 'bg-slate-200 text-slate-700' }}">{{ $item->category }}</span></span>
                <span>Sat. Hitung: <b class="text-slate-700">{{ $item->unit_count ?? '-' }}</b></span>
                <span>Lokasi: <span class="font-mono">{{ $item->rack ?? '-' }}</span></span>
                <span>Safety Stock: <b class="font-mono">{{ $item->safety_stock }}</b></span>
            </div>
        </div>
        <div class="text-right">
            <div class="label-caps text-slate-400">Sisa Saldo</div>
            <div class="font-mono text-3xl font-bold tabular">{{ $rows->sum('quantity') }}</div>
        </div>
    </div>
</div>

{{-- STOCK CARD TABLE --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-semibold">Riwayat Mutasi — Saldo Berjalan</h2>
        <a href="{{ route('kartu-stok.export', ['sku' => $item->sku]) }}" class="text-xs text-brand hover:underline">Export ke Excel</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50 whitespace-nowrap">
                    <th class="px-4 py-2.5 font-medium">Tanggal</th>
                    <th class="px-3 py-2.5 font-medium">No. Dokumen</th>
                    <th class="px-3 py-2.5 text-center font-medium">Jenis Pergerakan</th>
                    <th class="px-3 py-2.5 text-right font-medium">Masuk (+)</th>
                    <th class="px-3 py-2.5 text-right font-medium">Keluar (−)</th>
                    <th class="px-3 py-2.5 text-right font-medium font-bold">Saldo</th>
                    <th class="px-3 py-2.5 font-medium">User</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $i => $r)
                @php
                    $c = ['Inbound'=>'text-green-700 bg-green-50 border-green-300','Outbound'=>'text-brand bg-blue-50 border-blue-300','Opname'=>'text-purple-600 bg-purple-50 border-purple-300','Waste'=>'text-red-600 bg-red-50 border-red-300'][$r->type] ?? 'text-slate-600 bg-slate-100 border-slate-300';
                @endphp
                <tr class="hover:bg-slate-50 {{ $i % 2 ? 'bg-slate-50/50' : '' }}">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500 tabular">{{ $r->created_at->format('d M Y H:i') }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ $r->document_number }}</td>
                    <td class="px-3 py-2.5 text-center"><span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ $c }}">{{ $r->type }}</span></td>
                    <td class="px-3 py-2.5 font-mono text-right tabular text-green-600">{{ $r->quantity > 0 ? '+'.$r->quantity : '' }}</td>
                    <td class="px-3 py-2.5 font-mono text-right tabular text-red-600">{{ $r->quantity < 0 ? $r->quantity : '' }}</td>
                    <td class="px-3 py-2.5 font-mono text-right tabular font-bold">{{ $r->balance }}</td>
                    <td class="px-3 py-2.5">{{ $r->pic ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">Belum ada mutasi untuk item ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white border border-slate-200 rounded-lg px-5 py-12 text-center">
    <p class="text-slate-500 font-medium">Belum ada data barang</p>
</div>
@endif

{{-- AUDIT TRAIL --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden mt-5">
    <div class="px-5 py-3 border-b border-slate-200"><h2 class="font-semibold">Immutable Audit Trail</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50">
                    <th class="px-4 py-2.5 font-medium">Timestamp</th>
                    <th class="px-3 py-2.5 font-medium">User</th>
                    <th class="px-3 py-2.5 font-medium">Aksi</th>
                    <th class="px-3 py-2.5 font-medium">Objek</th>
                    <th class="px-4 py-2.5 font-medium">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $a)
                <tr class="hover:bg-slate-50 {{ $loop->index % 2 ? 'bg-slate-50/50' : '' }}">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500 tabular">{{ $a->created_at->format('d M Y H:i') }}</td>
                    <td class="px-3 py-2.5">{{ $a->email }}</td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ $a->action=='Create' ? 'bg-green-50 border-green-300 text-green-700' : ($a->action=='Inbound' ? 'bg-green-50 border-green-300 text-green-700' : ($a->action=='Outbound' ? 'bg-blue-50 border-blue-300 text-brand' : ($a->action=='Waste' ? 'bg-red-50 border-red-300 text-red-600' : 'bg-amber-50 border-amber-300 text-amber-700'))) }}">{{ $a->action }}</span>
                    </td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $a->object ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $a->description ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">Belum ada aktivitas tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>