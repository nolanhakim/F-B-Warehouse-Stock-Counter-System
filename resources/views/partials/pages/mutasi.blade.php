@php
$typeColors = [
    'Inbound' => 'text-green-700 bg-green-50 border-green-300',
    'Outbound' => 'text-brand bg-blue-50 border-blue-300',
    'Waste' => 'text-red-600 bg-red-50 border-red-300',
    'Opname' => 'text-slate-600 bg-slate-100 border-slate-300',
];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Mutasi Stok</h1>
    <div class="flex gap-2">
        <button onclick="openMutasiModal('Inbound')" class="text-sm font-medium px-4 py-2 rounded-md text-white bg-green-600 hover:bg-green-700">+ Inbound</button>
        <button onclick="openMutasiModal('Outbound')" class="text-sm font-medium px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">+ Outbound</button>
        <button onclick="openMutasiModal('Waste')" class="text-sm font-medium px-4 py-2 rounded-md text-white bg-red-600 hover:bg-red-700">+ Waste</button>
    </div>
</div>

{{-- FILTER --}}
<form method="GET" action="{{ route('mutasi') }}" class="bg-white border border-slate-200 rounded-lg p-3 mb-4 flex flex-wrap items-end gap-3 text-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Jenis</label>
        <select name="type" class="px-3 py-2 rounded-md border border-slate-300">
            <option value="">Semua Jenis</option>
            @foreach(['Inbound','Outbound','Waste','Opname'] as $t)
            <option value="{{ $t }}" {{ ($filters['type'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Status</label>
        <select name="status" class="px-3 py-2 rounded-md border border-slate-300">
            <option value="">Semua Status</option>
            @foreach(['approved'=>'Disetujui','pending'=>'Menunggu','rejected'=>'Ditolak'] as $k => $v)
            <option value="{{ $k }}" {{ ($filters['status'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Dari</label>
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="px-3 py-2 rounded-md border border-slate-300">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Sampai</label>
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="px-3 py-2 rounded-md border border-slate-300">
    </div>
    <button type="submit" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-medium">Filter</button>
    <a href="{{ route('mutasi') }}" class="px-3 py-2 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm">Reset</a>
    <a href="{{ route('mutasi.export', request()->query()) }}" class="px-3 py-2 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm">Export CSV</a>
</form>

{{-- MUTATION TABLE --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200">
        <h2 class="font-semibold text-lg">Riwayat Mutasi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50 whitespace-nowrap">
                    <th class="px-4 py-2.5 font-medium">No. Dokumen</th>
                    <th class="px-3 py-2.5 text-center font-medium">Jenis</th>
                    <th class="px-3 py-2.5 font-medium">SKU</th>
                    <th class="px-3 py-2.5 font-medium">Barang</th>
                    <th class="px-3 py-2.5 font-medium">Batch</th>
                    <th class="px-3 py-2.5 text-center font-medium">Exp</th>
                    <th class="px-3 py-2.5 text-right font-medium">Qty</th>
                    <th class="px-3 py-2.5 font-medium">Rak</th>
                    <th class="px-3 py-2.5 font-medium">PIC</th>
                    <th class="px-4 py-2.5 text-center font-medium">Waktu</th>
                    <th class="px-4 py-2.5 text-center font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mutations as $m)
                <tr class="hover:bg-slate-50 {{ $loop->index % 2 ? 'bg-slate-50/50' : '' }}">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $m->document_number }}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex whitespace-nowrap rounded border px-2 py-0.5 text-[10px] font-semibold {{ $typeColors[$m->type] ?? 'text-slate-600 bg-slate-100 border-slate-300' }}">{{ $m->type }}</span>
                    </td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $m->item->sku ?? '-' }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap">{{ $m->item->name ?? '-' }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ $m->batch ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-center font-mono text-xs {{ $m->expired_at ? ($m->expired_at->isPast() ? 'text-red-600 font-semibold' : ($m->expired_at->diffInDays(now()) <= 30 ? 'text-amber-600' : 'text-slate-500')) : '' }}">{{ $m->expired_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-3 py-2.5 font-mono text-right tabular font-semibold {{ $m->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $m->rack ?? '-' }}</td>
                    <td class="px-3 py-2.5">{{ $m->pic ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-center font-mono text-xs text-slate-500 tabular">{{ $m->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @php
                            $statusConfig = [
                                'pending'  => ['label' => 'Menunggu Persetujuan', 'class' => 'bg-amber-50 text-amber-700 border-amber-300'],
                                'approved' => ['label' => 'Disetujui',   'class' => 'bg-green-50 text-green-700 border-green-300'],
                                'rejected' => ['label' => 'Ditolak',     'class' => 'bg-red-50 text-red-600 border-red-300'],
                            ];
                            $sc = $statusConfig[$m->status] ?? $statusConfig['pending'];
                            $canApprove = session('role') !== 'staff' && $m->type === 'Waste' && $m->status === 'pending';
                        @endphp
                        <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ $sc['class'] }}">{{ $sc['label'] }}</span>
                        @if($canApprove)
                        <div class="mt-1 flex justify-center gap-1">
                            <form method="POST" action="{{ route('waste.approve', $m) }}">
                                @csrf
                                <button type="submit" class="text-xs px-1.5 py-0.5 rounded bg-green-600 hover:bg-green-700 text-white font-medium">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('waste.reject', $m) }}">
                                @csrf
                                <button type="submit" class="text-xs px-1.5 py-0.5 rounded border border-slate-300 hover:bg-slate-50 text-slate-600 font-medium">Tolak</button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="px-4 py-12 text-center">
                        <p class="text-slate-500 font-medium">Belum ada data mutasi</p>
                        <p class="text-slate-400 text-xs mt-1">Mutasi akan muncul setelah ada transaksi inbound/outbound.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $mutations->links() }}
</div>

{{-- MODAL TAMBAH MUTASI --}}
<div id="modalMutasi" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 id="mutasiModalTitle" class="text-lg font-bold">Tambah Mutasi</h2>
            <button onclick="closeMutasiModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('mutasi.store') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="type" id="mutasiType" value="Inbound">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Barang</label>
                <input type="text" id="itemSearch" list="datalistItems" required autocomplete="off" placeholder="Ketik SKU / nama barang..."
                    oninput="syncItem(this)" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <input type="hidden" name="item_id" id="itemId" value="">
                <datalist id="datalistItems">
                    @foreach($items as $it)
                    <option value="{{ $it->sku }}" label="{{ $it->name }}">{{ $it->name }}</option>
                    @endforeach
                </datalist>
                <p id="itemHint" class="hidden text-xs text-red-600 mt-1">SKU tidak ditemukan. Pilih dari saran.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Batch</label>
                    <input type="text" name="batch" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="LOT/BATCH">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Expired</label>
                    <input type="date" name="expired_at" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Qty</label>
                    <input type="number" name="quantity" required min="1" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="0">
                </div>
                <div></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Rak</label>
                    <input type="text" name="rack" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="CHILLER-01-A2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">PIC</label>
                    <input type="text" name="pic" value="{{ session('name') }}" readonly class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm bg-slate-50 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeMutasiModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-medium transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
const itemOptions = new Map([@foreach($items as $it)['{{ $it->sku }}', {{ $it->id }}],@endforeach]);
function syncItem(el) {
    const sku = el.value.trim();
    const id = itemOptions.get(sku);
    document.getElementById('itemId').value = id || '';
    document.getElementById('itemHint').classList.toggle('hidden', !!id || !sku);
    el.classList.toggle('border-red-300', !!sku && !id);
}
function openMutasiModal(type) {
    document.getElementById('mutasiType').value = type;
    document.getElementById('mutasiModalTitle').textContent = 'Catat ' + type;
    document.getElementById('itemSearch').value = '';
    document.getElementById('itemId').value = '';
    document.getElementById('itemHint').classList.add('hidden');
    document.getElementById('modalMutasi').classList.remove('hidden');
}
function closeMutasiModal() { document.getElementById('modalMutasi').classList.add('hidden'); }
</script>
