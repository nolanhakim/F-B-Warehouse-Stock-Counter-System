@extends('layouts.app')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Manajemen Pengguna</h1>
    <button onclick="openModal()" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2 rounded-md active:scale-[.99] transition">+ Tambah User</button>
</div>

{{-- STAT USER --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
    @php
        $roleCount = $allUsers->count();
        $roleAktif = $allUsers->where('is_active', true)->count();
        $roleSuper = $allUsers->where('role', 'super')->count();
        $roleAdmin = $allUsers->where('role', 'admin')->count();
        $roleStaff = $allUsers->where('role', 'staff')->count();
    @endphp
    @foreach([
        ['Total User',$roleCount,'text-slate-900'],
        ['Aktif',$roleAktif,'text-green-600'],
        ['Super Admin',$roleSuper,'text-red-600'],
        ['Admin Gudang',$roleAdmin,'text-slate-700'],
        ['Karyawan',$roleStaff,'text-brand'],
    ] as $s)
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="label-caps text-slate-500">{{ $s[0] }}</div>
        <div data-pop class="mt-1 font-mono text-2xl font-bold tabular {{ $s[2] }}">{{ $s[1] }}</div>
    </div>
    @endforeach
</div>

{{-- FILTER --}}
<div class="bg-white border border-slate-200 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-3 text-sm">
    <input id="searchUser" oninput="filterUsers()" class="flex-1 min-w-[200px] px-3 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Cari nama / email...">
    <select id="filterRole" onchange="filterUsers()" class="px-3 py-2 rounded-md border border-slate-300"><option value="">Semua Role</option><option value="super">Super Admin</option><option value="admin">Admin Gudang</option><option value="staff">Karyawan</option></select>
    <select id="filterStatus" onchange="filterUsers()" class="px-3 py-2 rounded-md border border-slate-300"><option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
</div>

{{-- TABLE --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50 whitespace-nowrap">
                    <th class="px-4 py-2.5 font-medium">User</th>
                    <th class="px-3 py-2.5 font-medium">Email</th>
                    <th class="px-3 py-2.5 text-center font-medium">Role</th>
                    <th class="px-3 py-2.5 text-center font-medium">Status</th>
                    <th class="px-4 py-2.5 text-center font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                $roleLabels = ['super' => 'Super Admin', 'admin' => 'Admin Gudang', 'staff' => 'Karyawan'];
                @endphp
                @foreach($users as $u)
                @php $status = $u->is_active ? 'Aktif' : 'Nonaktif'; @endphp
                <tr data-role="{{ $u->role }}" data-active="{{ $u->is_active }}" data-search="{{ strtolower($u->name . ' ' . $u->email) }}" class="user-row hover:bg-slate-50 {{ $loop->index % 2 ? 'bg-slate-50/50' : '' }}">
                    <td class="px-4 py-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(mb_substr(trim($u->name),0,2)) }}</span>
                            <span class="font-medium whitespace-nowrap">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-2.5 font-mono text-xs text-slate-500 whitespace-nowrap">{{ $u->email }}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap
                        {{ $u->role=='super' ? 'bg-red-50 border border-red-300 text-red-600' : ($u->role=='admin' ? 'bg-slate-100 border border-slate-300 text-slate-700' : 'bg-blue-50 border border-blue-300 text-brand') }}">{{ $roleLabels[$u->role] ?? 'Karyawan' }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold {{ $status=='Aktif' ? 'text-green-600' : 'text-slate-400' }}"><span class="w-1.5 h-1.5 rounded-full {{ $status=='Aktif' ? 'bg-green-500' : 'bg-slate-300' }}"></span>{{ $status }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                        <div class="inline-flex gap-1.5">
                            <button onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ $u->role }}', {{ $u->is_active ? '1' : '0' }})" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50 text-xs text-slate-600">Edit</button>
                            <button type="button" onclick="openDeleteModal({{ $u->id }}, '{{ addslashes($u->name) }}')" class="px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50 text-xs">Hapus</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>

<script>
function filterUsers() {
    const q = document.getElementById('searchUser').value.toLowerCase();
    const role = document.getElementById('filterRole').value;
    const status = document.getElementById('filterStatus').value;
    document.querySelectorAll('.user-row').forEach(tr => {
        const matchSearch = !q || tr.dataset.search.includes(q);
        const matchRole = !role || tr.dataset.role === role;
        const matchStatus = status === '' || tr.dataset.active === status;
        tr.style.display = (matchSearch && matchRole && matchStatus) ? '' : 'none';
    });
}
function openModal() { document.getElementById('modalAdd').classList.remove('hidden'); }
function closeModal() { document.getElementById('modalAdd').classList.add('hidden'); }
function openDeleteModal(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteForm').action = '/users/' + id;
    document.getElementById('modalDelete').classList.remove('hidden');
}
function closeDeleteModal() { document.getElementById('modalDelete').classList.add('hidden'); }
function openEditModal(id, name, email, role, active) {
    const form = document.getElementById('editForm');
    form.action = '/users/' + id;
    form.querySelector('[name="name"]').value = name;
    form.querySelector('[name="email"]').value = email;
    form.querySelector('[name="role"]').value = role;
    form.querySelector('[name="is_active"]').value = active;
    document.getElementById('modalEdit').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('modalEdit').classList.add('hidden'); }
</script>

{{-- MODAL TAMBAH USER --}}
<div id="modalAdd" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold">Tambah User Baru</h2>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Masukkan nama">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="contoh@fnb.id">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 pr-10 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Minimal 6 karakter">
                    <button type="button" onclick="togglePass(this)" tabindex="-1" title="Lihat password" class="absolute inset-y-0 right-0 px-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5 eye-open" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select name="role" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="staff">Karyawan</option>
                    <option value="admin">Admin Gudang</option>
                    <option value="super">Super Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-medium transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS USER --}}
<div id="modalDelete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold">Hapus User</h2>
            <button onclick="closeDeleteModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-5">
            <p class="text-sm text-slate-600">Yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
            <p class="text-xs text-slate-400 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
            <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">Hapus</button>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT USER --}}
<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold">Edit User</h2>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-xs text-slate-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                <div class="relative">
                    <input type="password" name="password" minlength="6" class="w-full px-3 py-2 pr-10 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Kosongkan jika tidak diubah">
                    <button type="button" onclick="togglePass(this)" tabindex="-1" title="Lihat password" class="absolute inset-y-0 right-0 px-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5 eye-open" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select name="role" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="staff">Karyawan</option>
                    <option value="admin">Admin Gudang</option>
                    <option value="super">Super Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="is_active" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-md border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-medium transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection