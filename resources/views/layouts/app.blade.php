<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'Gudang F&B' }} — F&B Warehouse System</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ url('/') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] font-sans min-h-screen">

    {{-- OFFLINE BANNER --}}
    <div class="hidden bg-amber-100 border-b border-amber-300 text-amber-900 text-xs px-4 py-2">
        Koneksi terputus. Data hitungan tersimpan lokal di browser (IndexedDB) dan akan disinkronkan saat online.
    </div>

    {{-- ======== DESKTOP SIDEBAR ======== --}}
    <aside id="sidebar" class="hidden lg:flex fixed inset-y-0 left-0 w-60 flex-col bg-canvas text-ink transition-all duration-200">
        <div class="h-16 flex items-center gap-2.5 px-5 border-b border-edge">
            <div class="w-9 h-9 rounded-lg bg-brand flex items-center justify-center font-bold text-xl">F</div>
            <div>
                <div class="font-bold leading-tight">F&B Warehouse</div>
                <div class="text-[10px] text-muted label-caps">Stock Counter System</div>
            </div>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto text-sm">
            @php
                $role = session('role', 'staff');
                $nav = [
                    ['dashboard', 'Dashboard', 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', ['admin','staff','super']],
                    ['master', 'Master Barang', 'M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z', ['admin','staff','super']],
                    ['mutasi', 'Mutasi Stok', 'M8 5v14l11-7z', ['admin','staff','super']],
                    ['opname', 'Sesi Stock Opname', 'M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z', ['admin','staff','super']],
                    ['kartu-stok', 'Kartu Stok & Audit', 'M4 6h2v2H4V6zm0 5h2v2H4v-2zm0 5h2v2H4v-2zm16-8H10v2h10V8zm0 5H10v2h10v-2zm0 5H10v2h10v-2z', ['admin','staff','super']],
                    ['waste', 'Waste & Approval', 'M12 2l1 5h6l-4 6 1 5-4-3-4 3 1-5-4-6h6l1-5z', ['admin','staff','super']],
                    ['alerts', 'Alert & Expired', 'M10 20h4a2 2 0 0 1-4 0zm8-6v-4a6 6 0 0 0-12 0v4l-2 2v1h16v-1l-2-2z', ['admin','staff','super']],
                    ['users', 'Manajemen User', 'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z', ['super']],
                    ['logs', 'Log Aktivitas', 'M4 6h2v2H4V6zm0 5h2v2H4v-2zm0 5h2v2H4v-2zm16-8H10v2h10V8zm0 5H10v2h10v-2zm0 5H10v2h10v-2z', ['super']],
                ];
            @endphp
            @foreach($nav as $n)
                @if(in_array($role, $n[3]))
                <x-nav-link :active="$active==$n[0]" href="/{{ $n[0] }}" :label="$n[1]" :icon="$n[2]"/>
                @endif
            @endforeach
        </nav>
        <div class="p-3 border-t border-edge">
            @php
                $role = session('role', 'staff');
                $roleNames = ['admin' => 'Admin Gudang', 'staff' => 'Karyawan', 'super' => 'Super Admin'];
                $roleDesc = ['admin' => 'Warehouse Manager', 'staff' => 'Staff Gudang / Checker', 'super' => 'System Owner'];
                $userName = session('name', $roleNames[$role]);
                $initials = collect(explode(' ', $userName))->take(2)->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->join('');
                $profile = [
                    'admin' => ['email' => 'admin.gudang@fnb.id', 'last' => 'Baru saja', 'is_active' => true, 'desc' => 'Mengelola master data barang, konversi satuan, pemetaan rak, manajemen akun, serta memantau laporan valuasi gudang.'],
                    'staff' => ['email' => 'karyawan@fnb.id', 'last' => 'Baru saja', 'is_active' => true, 'desc' => 'Mencatat penerimaan & pengeluaran barang, mendata waste, dan menghitung fisik saat sesi stock opname.'],
                    'super' => ['email' => 'super.admin@fnb.id', 'last' => 'Baru saja', 'is_active' => true, 'desc' => 'Akses penuh seluruh fitur, termasuk manajemen pengguna dan pemantauan log aktivitas seluruh sistem.'],
                ];
                $pro = [
                    'email' => session('email', $profile[$role]['email']),
                    'is_active' => session('is_active', $profile[$role]['is_active']),
                    'last' => session('last_login_at') ? \Illuminate\Support\Carbon::parse(session('last_login_at'))->format('d M Y H:i') : 'Baru saja',
                    'desc' => $profile[$role]['desc'],
                ];
            @endphp
            <div class="flex items-center gap-2.5 px-2">
                <button type="button" onclick="openProfile()" title="Profil Saya" class="flex items-center gap-2.5 min-w-0 flex-1 text-left group">
                    <div class="w-8 h-8 rounded-full bg-brand-dark flex items-center justify-center text-white font-semibold text-xs shrink-0 group-hover:ring-2 group-hover:ring-brand transition">{{ $initials }}</div>
                    <div class="text-xs min-w-0">
                        <div class="font-semibold text-ink truncate group-hover:text-brand transition-colors">{{ $userName }}</div>
                        <div class="text-muted truncate">{{ $roleDesc[$role] }}</div>
                    </div>
                </button>
                <button type="button" onclick="openLogout()" title="Keluar" class="p-1.5 rounded-md text-muted hover:text-ink hover:bg-surface transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M16 17v-3H9v-4h7V7l5 5-5 5zM14 2a2 2 0 0 1 2 2v3h-2V4H4v16h10v-3h2v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h10z"/></svg>
                    </button>
            </div>
        </div>
    </aside>

    {{-- ======== DESKTOP MAIN ======== --}}
    <div class="lg:pl-60 flex flex-col min-h-screen">

        {{-- TOP BAR --}}
        <header class="h-16 flex items-center gap-4 px-4 sm:px-6 bg-white border-b border-slate-200">
            <button class="lg:hidden p-2" onclick="toggleMobile()">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
            </button>
            <div class="hidden lg:block text-sm text-muted">
                <span class="text-[#0F172A] font-medium">{{ $pageTitle ?? '' }}</span>
            </div>
            <div class="ml-auto flex items-center gap-3">
                @php
                $pendingWasteCount = \App\Models\Mutation::where('type', 'Waste')->where('status', 'pending')
                    ->when($role === 'staff', fn ($q) => $q->where('pic', session('name')))
                    ->count();
                @endphp
                @if($pendingWasteCount > 0)
                <a href="/waste" title="{{ $pendingWasteCount }} waste menunggu persetujuan" class="relative p-1.5 rounded-md text-muted hover:text-ink hover:bg-surface transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                    <span class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center tabular">{{ $pendingWasteCount > 99 ? '99+' : $pendingWasteCount }}</span>
                </a>
                @else
                <a href="/waste" title="Waste & Persetujuan" class="relative p-1.5 rounded-md text-muted hover:text-ink hover:bg-surface transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                </a>
                @endif
                <button type="button" onclick="openProfile()" title="Profil Saya" class="w-8 h-8 rounded-full bg-brand-dark text-white flex items-center justify-center text-xs font-semibold hover:ring-2 hover:ring-brand transition cursor-pointer">{{ $initials }}</button>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="px-6 py-3 text-[10px] text-slate-400 border-t border-slate-200">
            F&B Warehouse Management & Stock Counter — Internal System
        </footer>
    </div>

    {{-- ======== TABLET / MOBILE BOTTOM NAV ======== --}}
    <div class="lg:hidden fixed bottom-0 inset-x-0 h-16 bg-white border-t border-slate-200 flex items-stretch z-40">
        <button class="flex-1 flex flex-col items-center justify-center gap-0.5 {{ $active=='opname' ? 'text-brand' : 'text-slate-500' }}" onclick="go('/opname')">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/></svg>
            <span class="text-[10px] font-medium">Hitung</span>
        </button>
        <button class="flex-1 flex flex-col items-center justify-center gap-0.5 {{ in_array($active,['mutasi','waste']) ? 'text-brand' : 'text-slate-500' }}" onclick="go('/mutasi')">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span class="text-[10px] font-medium">Mutasi</span>
        </button>
        <button class="flex-1 flex flex-col items-center justify-center gap-0.5 {{ $active=='cari' ? 'text-brand' : 'text-slate-500' }}" onclick="go('/cari')">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 3a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM0 9.5a9.5 9.5 0 1 1 17 6l4.8 4.8-1.4 1.4-4.8-4.8A9.5 9.5 0 0 1 0 9.5z"/></svg>
            <span class="text-[10px] font-medium">Cari Stok</span>
        </button>
    </div>

    {{-- MOBILE SHEET --}}
    <div id="mobileSheet" class="hidden lg:hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" onclick="toggleMobile()"></div>
        <nav class="absolute left-0 top-0 bottom-0 w-64 bg-canvas text-ink flex flex-col p-4 space-y-1 text-sm">
            @foreach($nav as $n)
                @if(in_array($role, $n[3]))
                <a href="/{{ $n[0] }}" class="p-2.5 rounded-md hover:bg-surface {{ $active==$n[0] ? 'bg-surface text-brand' : '' }}">{{ $n[1] }}</a>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="lg:hidden pb-16"></div>

    <x-toast />

    @if(session()->has('role'))
    <button type="button" id="chatFab" onclick="toggleChat()" title="Tanya asisten gudang"
        class="fixed bottom-20 lg:bottom-6 right-4 sm:right-6 z-50 w-14 h-14 rounded-full bg-brand hover:bg-brand-dark text-white shadow-xl flex items-center justify-center active:scale-95 transition">
        <svg id="chatFabIcon" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7v3c0 1 .4 1.9 1 2.6V17a1 1 0 0 0 1 1h1v2a1 1 0 0 0 2 0v-2h4v2a1 1 0 0 0 2 0v-2h1a1 1 0 0 0 1-1v-2.4c.6-.7 1-1.6 1-2.6V9a7 7 0 0 0-7-7zM8 9.5A1.5 1.5 0 1 1 9.5 11 1.5 1.5 0 0 1 8 9.5zm8 0A1.5 1.5 0 1 1 17.5 11 1.5 1.5 0 0 1 16 9.5z"/></svg>
    </button>

    <div id="chatPanel" class="hidden fixed bottom-36 lg:bottom-24 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] max-w-sm bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden page-enter">
        <div class="bg-canvas text-ink px-4 py-3 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center font-bold">F</div>
            <div class="min-w-0 flex-1">
                <div class="font-semibold text-sm">Asisten Gudang</div>
                <div class="text-[11px] text-muted">Tanya stok, expired, atau cara pakai</div>
            </div>
            <button type="button" onclick="toggleChat()" class="p-1.5 rounded-md text-muted hover:text-ink hover:bg-surface transition" title="Tutup">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.3 5.7 12 12l-6.3-6.3-1.4 1.4L10.6 13.4l-6.3 6.3 1.4 1.4L12 14.8l6.3 6.3 1.4-1.4-6.3-6.3 6.3-6.3-1.4-1.4z"/></svg>
            </button>
        </div>
        <div id="chatLog" class="h-80 overflow-y-auto p-4 space-y-3 text-sm bg-slate-50">
            <div class="flex gap-2">
                <div class="max-w-[85%] bg-white border border-slate-200 rounded-lg px-3 py-2 shadow-sm">
                    Halo 👋 Aku bisa cek <b>stok live</b>, <b>expired FEFO</b>, atau pandu <b>inbound / outbound / waste / opname</b>. Coba tanya: <i>"stok menipis apa saja?"</i>
                </div>
            </div>
        </div>
        <div id="chatQuick" class="px-3 pt-2 pb-1 flex gap-1.5 flex-wrap bg-slate-50">
            <button type="button" onclick="askQuick(this)" data-q="Stok menipis apa saja?" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-300 bg-white hover:border-brand hover:text-brand transition">Stok menipis?</button>
            <button type="button" onclick="askQuick(this)" data-q="Apa yang mau expired dalam 30 hari?" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-300 bg-white hover:border-brand hover:text-brand transition">Mau expired?</button>
            <button type="button" onclick="askQuick(this)" data-q="Bagaimana cara mencatat waste?" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-300 bg-white hover:border-brand hover:text-brand transition">Cara waste?</button>
        </div>
        <form id="chatForm" class="p-3 bg-white border-t border-slate-200 flex gap-2">
            <input id="chatInput" type="text" autocomplete="off" maxlength="2000" placeholder="Tulis pertanyaan…"
                class="flex-1 min-w-0 px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <button type="submit" id="chatSend" class="px-3.5 py-2 rounded-md bg-brand hover:bg-brand-dark text-white text-sm font-semibold active:scale-95 transition shrink-0">Kirim</button>
        </form>
    </div>
    @endif

    {{-- PROFILE MODAL --}}
    <div id="profileModal" class="hidden fixed inset-0 z-[60]">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeProfile()"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-xl border border-slate-200 overflow-hidden page-enter">
                {{-- HEADER --}}
                <div class="relative bg-canvas text-ink p-6 pb-9">
                    <button type="button" onclick="closeProfile()" class="absolute top-3 right-3 p-1.5 rounded-md text-muted hover:text-ink hover:bg-surface transition-colors" title="Tutup">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.3 5.7 12 12l-6.3-6.3-1.4 1.4L10.6 13.4l-6.3 6.3 1.4 1.4L12 14.8l6.3 6.3 1.4-1.4-6.3-6.3 6.3-6.3-1.4-1.4z"/></svg>
                    </button>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-brand flex items-center justify-center text-white font-bold text-2xl ring-4 ring-edge shrink-0">{{ $initials }}</div>
                        <div class="min-w-0">
                            <div class="text-xl font-bold truncate">{{ $userName }}</div>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold bg-brand text-white">{{ $role }}</span>
                                <span class="text-sm text-muted">{{ $roleDesc[$role] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-x-4 gap-y-4 text-sm">
                        <div class="col-span-2">
                            <div class="label-caps text-slate-400">Email</div>
                            <div class="mt-1 font-mono">{{ $pro['email'] }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" required value="{{ $userName }}" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Password Baru <span class="text-slate-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                            <div class="relative">
                                <input type="password" name="password" minlength="6" autocomplete="new-password" class="w-full px-3 py-2 pr-10 rounded-md border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Minimal 6 karakter">
                                <button type="button" onclick="togglePass(this)" tabindex="-1" title="Lihat password" class="absolute inset-y-0 right-0 px-2.5 text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5 eye-open" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                                    <svg class="w-5 h-5 eye-closed hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="w-full h-11 rounded-md bg-brand hover:bg-brand-dark text-white font-semibold active:scale-[.99] transition">Simpan Profil</button>
                    </form>
                    <div class="mt-5 rounded-md bg-slate-50 border border-slate-200 p-3.5 text-xs text-slate-600">
                        <div class="label-caps text-slate-400 mb-1">Tentang Peran</div>
                        {{ $pro['desc'] }}
                    </div>
                    <button type="button" onclick="closeProfile()" class="mt-5 w-full h-11 rounded-md border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold active:scale-[.99] transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- LOGOUT CONFIRM MODAL --}}
    <div id="logoutModal" class="hidden fixed inset-0 z-[60]">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeLogout()"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
            <div class="w-full max-w-sm bg-white rounded-lg shadow-xl border border-slate-200 p-6 page-enter">
                <div class="w-12 h-12 rounded-full bg-red-50 border border-red-200 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M16 17v-3H9v-4h7V7l5 5-5 5zM14 2a2 2 0 0 1 2 2v3h-2V4H4v16h10v-3h2v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h10z"/></svg>
                </div>
                <h3 class="mt-4 text-center text-lg font-bold">Keluar dari sistem?</h3>
                <p class="mt-1 text-center text-sm text-slate-500">Anda harus masuk kembali untuk bertransaksi.</p>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" onclick="closeLogout()" class="h-11 rounded-md border border-slate-300 hover:bg-slate-50 font-semibold text-slate-700 active:scale-[.99] transition">Batal</button>
                    <form method="POST" action="/logout" class="contents">
                        @csrf
                        <button type="submit" class="h-11 rounded-md bg-red-600 hover:bg-red-700 text-white font-semibold active:scale-[.99] transition">Ya, Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePass(btn) {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.querySelector('.eye-open').classList.toggle('hidden', show);
        btn.querySelector('.eye-closed').classList.toggle('hidden', !show);
    }
    </script>
</body>
</html>
