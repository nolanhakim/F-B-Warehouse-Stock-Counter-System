@php
$cat = ['Dry Goods'=>'bg-slate-200 text-slate-700','Chilled'=>'bg-[#E0F2FE] text-[#0369A1]','Frozen'=>'bg-[#EDE9FE] text-[#6D28D9]','Packaging'=>'bg-[#FEF3C7] text-[#B45309]'];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Master Barang & Satuan</h1>
    <button onclick="openItemModal()" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2 rounded-md">+ Tambah Barang</button>
</div>

{{-- FILTER --}}
<div class="bg-white border border-slate-200 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-3 text-sm">
    <input id="searchItem" class="flex-1 min-w-[200px] px-3 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Cari nama / SKU...">
    <select id="filterItemCat" class="px-3 py-2 rounded-md border border-slate-300"><option value="">Semua Kategori</option><option>Dry Goods</option><option>Chilled</option><option>Frozen</option><option>Packaging</option></select>
    <select id="filterItemStatus" class="px-3 py-2 rounded-md border border-slate-300"><option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
</div>

<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50 whitespace-nowrap">
                    <th class="px-4 py-2.5 sticky left-0 bg-slate-50 font-medium">SKU</th>
                    <th class="px-4 py-2.5 font-medium">Nama Barang</th>
                    <th class="px-3 py-2.5 text-center font-medium">Kategori</th>
                    <th class="px-3 py-2.5 font-medium">Sat. Beli</th>
                    <th class="px-3 py-2.5 font-medium">Sat. Hitung</th>
                    <th class="px-3 py-2.5 text-center font-mono font-medium">Rasio</th>
                    <th class="px-3 py-2.5 text-right font-medium">Safety Stock</th>
                    <th class="px-3 py-2.5 font-medium">Rak</th>
                    <th class="px-4 py-2.5 text-center font-medium">Status</th>
                    @if(in_array(session('role'), ['admin', 'super']))
                    <th class="px-4 py-2.5 text-center font-medium">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $i)
                <tr data-icat="{{ $i->category }}" data-iactive="{{ $i->is_active ? '1' : '0' }}" data-isearch="{{ strtolower($i->name . ' ' . $i->sku) }}" class="item-row hover:bg-slate-50 {{ $loop->index % 2 ? 'bg-slate-50/50' : '' }}">
                    <td class="px-4 py-2.5 sticky left-0 bg-inherit font-mono text-xs">{{ $i->sku }}</td>
                    <td class="px-4 py-2.5 font-medium whitespace-nowrap">{{ $i->name }}</td>
                    <td class="px-3 py-2.5 text-center"><span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold {{ $cat[$i->category] ?? 'bg-slate-200 text-slate-700' }}">{{ $i->category }}</span></td>
                    <td class="px-3 py-2.5 whitespace-nowrap">{{ $i->unit_buy ?? '-' }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap">{{ $i->unit_count ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-center font-mono text-xs tabular">{{ $i->ratio ?? '-' }}</td>
                    <td class="px-3 py-2.5 text-right font-mono tabular">{{ $i->safety_stock }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $i->rack ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold {{ $i->is_active ? 'bg-[#F0FDF4] text-[#16A34A] border border-green-300' : 'bg-slate-100 text-slate-500 border border-slate-300' }}">{{ $i->is_active ? '●' : '○' }} {{ $i->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    @if(in_array(session('role'), ['admin', 'super']))
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                        <div class="inline-flex gap-1.5">
                            <button onclick="openEditItemModal({{ $i->id }}, '{{ addslashes($i->name) }}', '{{ $i->category }}', '{{ addslashes($i->unit_buy) }}', '{{ addslashes($i->unit_count) }}', '{{ addslashes($i->ratio) }}', {{ $i->safety_stock }}, '{{ addslashes($i->rack) }}', {{ $i->is_active ? '1' : '0' }})" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50 text-xs text-slate-600">Edit</button>
                            <button type="button" onclick="openDeleteItemModal({{ $i->id }}, '{{ addslashes($i->sku) }}', '{{ addslashes($i->name) }}')" class="px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50 text-xs">Hapus</button>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ in_array(session('role'), ['admin', 'super']) ? 10 : 9 }}" class="px-4 py-12 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <p class="text-slate-500 font-medium">Belum ada data barang</p>
                        <p class="text-slate-400 text-xs mt-1">Klik "+ Tambah Barang" untuk menambahkan data baru.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $items->links() }}
</div>

<script>
function filterItems() {
    const q = document.getElementById('searchItem')?.value.toLowerCase() ?? '';
    const cat = document.getElementById('filterItemCat')?.value ?? '';
    const status = document.getElementById('filterItemStatus')?.value ?? '';
    document.querySelectorAll('.item-row').forEach(tr => {
        const matchSearch = !q || tr.dataset.isearch.includes(q);
        const matchCat = !cat || tr.dataset.icat === cat;
        const matchStatus = status === '' || tr.dataset.iactive === status;
        tr.style.display = (matchSearch && matchCat && matchStatus) ? '' : 'none';
    });
}
document.getElementById('searchItem')?.addEventListener('input', filterItems);
document.getElementById('filterItemCat')?.addEventListener('change', filterItems);
document.getElementById('filterItemStatus')?.addEventListener('change', filterItems);
function openItemModal() { document.getElementById('modalItem').classList.remove('hidden'); }
function closeItemModal() { document.getElementById('modalItem').classList.add('hidden'); }
function openEditItemModal(id, name, category, unitBuy, unitCount, ratio, safetyStock, rack, active) {
    const form = document.getElementById('itemEditForm');
    form.action = '/master/' + id;
    form.querySelector('[name="name"]').value = name;
    form.querySelector('[name="category"]').value = category;
    form.querySelector('[name="unit_buy"]').value = unitBuy;
    form.querySelector('[name="unit_count"]').value = unitCount;
    form.querySelector('[name="ratio"]').value = ratio;
    form.querySelector('[name="safety_stock"]').value = safetyStock;
    form.querySelector('[name="rack"]').value = rack;
    form.querySelector('[name="is_active"]').value = active;
    document.getElementById('modalItemEdit').classList.remove('hidden');
}
function closeEditItemModal() { document.getElementById('modalItemEdit').classList.add('hidden'); }
function openDeleteItemModal(id, sku, name) {
    document.getElementById('deleteItemSku').textContent = sku + ' — ' + name;
    document.getElementById('itemDeleteForm').action = '/master/' + id;
    document.getElementById('modalItemDelete').classList.remove('hidden');
}
function closeDeleteItemModal() { document.getElementById('modalItemDelete').classList.add('hidden'); }
</script>

{{-- MODAL TAMBAH BARANG --}}
<div id="modalItem" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold">Tambah Barang</h2>
            <button onclick="closeItemModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('items.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                <select name="category" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="Dry Goods">Dry Goods</option>
                    <option value="Chilled">Chilled</option>
                    <option value="Frozen">Frozen</option>
                    <option value="Packaging">Packaging</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                <input type="text" name="name" required class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Nama barang">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan Beli</label>
                    <input type="text" name="unit_buy" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Karton">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan Hitung</label>
                    <input type="text" name="unit_count" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Pack">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Rasio</label>
                    <input type="text" name="ratio" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="1 = 12">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Safety Stock</label>
                    <input type="number" name="safety_stock" value="0" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rak</label>
                <input type="text" name="rack" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="CHILLER-01-A2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeItemModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-medium transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

@if(in_array(session('role'), ['admin', 'super']))
{{-- MODAL EDIT BARANG --}}
<div id="modalItemEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold">Edit Barang</h2>
            <button onclick="closeEditItemModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form id="itemEditForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                <select name="category" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="Dry Goods">Dry Goods</option>
                    <option value="Chilled">Chilled</option>
                    <option value="Frozen">Frozen</option>
                    <option value="Packaging">Packaging</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                <input type="text" name="name" required class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan Beli</label>
                    <input type="text" name="unit_buy" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Karton">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan Hitung</label>
                    <input type="text" name="unit_count" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Pack">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Rasio</label>
                    <input type="text" name="ratio" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="1 = 12">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Safety Stock</label>
                    <input type="number" name="safety_stock" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rak</label>
                <input type="text" name="rack" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="CHILLER-01-A2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditItemModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-medium transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS BARANG --}}
<div id="modalItemDelete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold">Hapus Barang</h2>
            <button onclick="closeDeleteItemModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-5">
            <p class="text-sm text-slate-600">Yakin ingin menghapus barang <strong id="deleteItemSku"></strong>?</p>
            <p class="text-xs text-slate-400 mt-1">Barang yang punya riwayat mutasi tidak bisa dihapus — nonaktifkan saja.</p>
        </div>
        <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
            <button onclick="closeDeleteItemModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
            <form id="itemDeleteForm" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">Hapus</button>
            </form>
        </div>
    </div>
</div>
@endif
