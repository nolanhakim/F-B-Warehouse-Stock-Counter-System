@php
$cat = ['Dry Goods'=>'bg-slate-200 text-slate-700','Chilled'=>'bg-[#E0F2FE] text-[#0369A1]','Frozen'=>'bg-[#EDE9FE] text-[#6D28D9]','Packaging'=>'bg-[#FEF3C7] text-[#B45309]'];
function stockClass($stock, $safety) {
    return $stock <= 0 ? 'text-red-600' : ($stock < $safety ? 'text-amber-600' : 'text-green-700');
}
@endphp

@if($session)
{{-- MODE: SESI OPNAME AKTIF --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Sesi Stock Opname</h1>
        <p class="text-sm text-slate-500 mt-0.5">Dimulai {{ $session->started_at->format('d M Y H:i') }} oleh {{ $session->started_by ?? '-' }} · {{ $items->count() }} item</p>
    </div>
    <div class="inline-flex rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">● Sesi Berlangsung</div>
</div>

<form method="POST" id="opnameForm">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($items as $q)
        @php
        $physical = $session->counts[$q['sku']] ?? $q['stock'];
        @endphp
        <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col gap-2">
            <div class="flex items-start justify-between gap-2">
                <span class="font-medium leading-snug">{{ $q['name'] }}</span>
                <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-semibold {{ $cat[$q['category']] ?? 'bg-slate-200 text-slate-700' }}">{{ $q['category'] }}</span>
            </div>
            <div class="font-mono text-xs text-slate-500">{{ $q['sku'] }} · Rak {{ $q['rack'] ?? '-' }}</div>
            <div class="grid grid-cols-2 gap-2 mt-2 pt-3 border-t border-slate-100">
                <div>
                    <div class="label-caps text-slate-400 text-[10px]">Stok Sistem {{ $q['unit_count'] ? '(' . $q['unit_count'] . ')' : '' }}</div>
                    <div id="sys-{{ $q['sku'] }}" class="font-mono text-2xl font-bold tabular leading-none mt-1 {{ stockClass($q['stock'], $q['safety_stock']) }}">{{ $q['stock'] }}</div>
                </div>
                <div>
                    <div class="label-caps text-slate-400 text-[10px]">Hitung Fisik</div>
                    <input type="number" min="0" name="counts[{{ $q['sku'] }}]" data-sku="{{ $q['sku'] }}" value="{{ $physical }}"
                        class="count-input w-full mt-1 px-2 py-1.5 rounded-md border border-slate-300 text-sm font-mono tabular focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div class="text-[10px] font-semibold text-slate-400">Selisih: <span id="diff-{{ $q['sku'] }}" class="diff-value font-mono">0</span></div>
        </div>
        @empty
        <div class="col-span-full bg-white border border-slate-200 rounded-lg px-5 py-12 text-center">
            <p class="text-slate-500 font-medium">Tidak ada barang aktif untuk dihitung.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-2">
        <button type="submit" formaction="{{ route('opname.finish') }}" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Selesai & Simpan Selisih</button>
        <button type="submit" formaction="{{ route('opname.draft') }}" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Simpan Draft</button>
        <button type="button" onclick="cancelOpname()" class="ml-auto px-4 py-2 rounded-md border border-red-300 text-red-600 hover:bg-red-50 text-sm">Batalkan Sesi</button>
    </div>
</form>

<form id="opnameCancel" method="POST" action="{{ route('opname.cancel') }}" class="hidden">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function cancelOpname() {
    Swal.fire({
        title: 'Batalkan sesi opname?',
        text: 'Hasil hitungan fisik akan dibuang.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#DC2626',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('opnameCancel').submit();
        }
    });
}
</script>

@else
{{-- MODE: PEMBAYAN / TIDAK ADA SESI AKTIF --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Stock Opname</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $items->count() }} item · tanpa sesi berjalan</p>
    </div>
    <form method="POST" action="{{ route('opname.start') }}">
        @csrf
        <button type="submit" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2 rounded-md">+ Mulai Opname</button>
    </form>
</div>

@php
    $healthy = $items->filter(fn($q) => $q['stock'] > 0 && $q['stock'] >= $q['safety_stock'])->count();
    $low = $items->filter(fn($q) => $q['stock'] > 0 && $q['stock'] < $q['safety_stock'])->count();
    $out = $items->filter(fn($q) => $q['stock'] <= 0)->count();
@endphp

<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="text-xs text-slate-500 label-caps">Aman</div>
        <div class="text-2xl font-bold text-green-700 mt-1">{{ $healthy }}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="text-xs text-slate-500 label-caps">Menipis (&lt; safety stock)</div>
        <div class="text-2xl font-bold text-amber-600 mt-1">{{ $low }}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="text-xs text-slate-500 label-caps">Habis / Minus</div>
        <div class="text-2xl font-bold text-red-600 mt-1">{{ $out }}</div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($items as $q)
    <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between gap-2">
            <span class="font-medium leading-snug">{{ $q['name'] }}</span>
            <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-semibold {{ $cat[$q['category']] ?? 'bg-slate-200 text-slate-700' }}">{{ $q['category'] }}</span>
        </div>
        <div class="font-mono text-xs text-slate-500">{{ $q['sku'] }} · Rak {{ $q['rack'] ?? '-' }}</div>
        <div class="mt-2 pt-3 border-t border-slate-100 flex items-end justify-between">
            <div>
                <div class="label-caps text-slate-400 text-[10px]">Stok {{ $q['unit_count'] ? '(' . $q['unit_count'] . ')' : '' }}</div>
                <div data-sku="{{ $q['sku'] }}" data-safety="{{ $q['safety_stock'] }}" id="stock-{{ $q['sku'] }}" class="font-mono text-3xl font-bold tabular leading-none mt-1 {{ stockClass($q['stock'], $q['safety_stock']) }}">{{ $q['stock'] }}</div>
            </div>
            @if($q['stock'] <= 0)
            <span class="text-[10px] font-semibold rounded px-1.5 py-0.5 bg-red-50 text-red-600 border border-red-300">HABIS</span>
            @elseif($q['stock'] < $q['safety_stock'])
            <div class="text-right">
                <span class="text-[10px] font-semibold rounded px-1.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-300">MENIPIS</span>
                <div class="font-mono text-[10px] text-slate-400 mt-1">safety {{ $q['safety_stock'] }}</div>
            </div>
            @else
            <span class="text-[10px] font-semibold rounded px-1.5 py-0.5 bg-green-50 text-green-700 border border-green-300">AMAN</span>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white border border-slate-200 rounded-lg px-5 py-12 text-center">
        <p class="text-slate-500 font-medium">Belum ada data barang</p>
    </div>
    @endforelse
</div>

<script>
async function pollStock() {
    if (document.visibilityState !== 'visible') return;
    try {
        const res = await fetch('{{ route('opname.data') }}?v=' + Date.now(), { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        document.querySelectorAll('[data-sku]').forEach(el => {
            const item = data.items.find(x => x.sku === el.dataset.sku);
            if (!item) return;
            const safety = Number(el.dataset.safety || 0);
            el.textContent = item.stock;
            el.className = el.className.replace(/text-(green|amber|red)-\d{3}/, item.stock <= 0 ? 'text-red-600' : (item.stock < safety ? 'text-amber-600' : 'text-green-700'));
        });
    } catch (e) { /* poll next tick */ }
}
setInterval(pollStock, 5000);
document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') pollStock(); });
</script>
@endif

<script>
document.querySelectorAll('.count-input').forEach(input => {
    input.addEventListener('input', () => {
        const sku = input.dataset.sku;
        const sys = document.getElementById('sys-' + sku)?.textContent || '0';
        const diff = (Number(input.value) || 0) - Number(sys);
        const el = document.getElementById('diff-' + sku);
        el.textContent = diff > 0 ? '+' + diff : diff;
        el.className = 'diff-value font-mono ' + (diff < 0 ? 'text-red-600' : (diff > 0 ? 'text-green-600' : 'text-slate-400'));
    });
});
</script>