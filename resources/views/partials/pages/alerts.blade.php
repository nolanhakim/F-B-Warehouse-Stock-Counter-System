<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Alert & Kedaluwarsa</h1>
</div>

<div class="flex flex-wrap gap-3 mb-5">
    <div class="bg-white border border-slate-200 rounded-lg px-4 py-3 flex items-center gap-3">
        <span class="rounded border px-2 py-1 text-[10px] font-bold bg-[#FEF2F2] text-[#DC2626] border-red-200">EXPIRED</span>
        <span class="text-xs text-slate-500">{{ $counts['danger'] }} batch — lewat tanggal kedaluwarsa</span>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg px-4 py-3 flex items-center gap-3">
        <span class="rounded border px-2 py-1 text-[10px] font-bold bg-[#FFFBEB] text-[#D97706] border-amber-200">H-7</span>
        <span class="text-xs text-slate-500">{{ $counts['h7'] }} batch — 1 s/d 7 hari</span>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg px-4 py-3 flex items-center gap-3">
        <span class="rounded border px-2 py-1 text-[10px] font-bold bg-[#FFFBEB] text-[#D97706] border-amber-200">H-30</span>
        <span class="text-xs text-slate-500">{{ $counts['h30'] }} batch — 8 s/d 30 hari</span>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50 whitespace-nowrap">
                    <th class="px-4 py-2.5 font-medium">Tingkat</th>
                    <th class="px-3 py-2.5 font-medium">SKU</th>
                    <th class="px-3 py-2.5 font-medium">Nama Barang</th>
                    <th class="px-3 py-2.5 font-medium">Batch</th>
                    <th class="px-3 py-2.5 font-medium">Rak</th>
                    <th class="px-3 py-2.5 text-center font-medium">Qty Bersisa</th>
                    <th class="px-3 py-2.5 text-center font-medium">Expired</th>
                    <th class="px-3 py-2.5 text-center font-medium">Sisa Hari</th>
                    <th class="px-4 py-2.5 text-center font-medium">Rekomendasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $a)
                @php $danger = $a['level'] == 'Danger'; @endphp
                <tr class="hover:bg-slate-50 {{ $danger ? 'bg-[#FEF2F2]' : 'bg-[#FFFBEB]' }}">
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center gap-1 rounded border px-2 py-0.5 text-[10px] font-bold {{ $danger ? 'bg-[#FEF2F2] text-[#DC2626] border-red-200' : 'bg-[#FFFBEB] text-[#D97706] border-amber-200' }}"><span class="w-1.5 h-1.5 rounded-full {{ $danger ? 'bg-red-600' : 'bg-amber-500' }}"></span>{{ $danger ? 'EXPIRED' : ($a['level'] == 'H7' ? 'H-7' : 'H-30') }}</span>
                    </td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $a['sku'] }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap font-medium">{{ $a['name'] }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ $a['batch'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $a['rack'] ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-center font-mono tabular">{{ $a['qty'] }}</td>
                    <td class="px-3 py-2.5 text-center font-mono text-xs tabular">{{ $a['expired_at'] }}</td>
                    <td class="px-3 py-2.5 text-center font-mono tabular font-bold {{ $danger ? 'text-red-600' : 'text-amber-600' }}">{{ $a['days'] > 0 ? '+' : '' }}{{ $a['days'] }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $a['recom'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center">
                        <p class="text-slate-500 font-medium">Tidak ada alert</p>
                        <p class="text-slate-400 text-xs mt-1">Isi tanggal expired pada mutasi inbound untuk memantau batch.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>