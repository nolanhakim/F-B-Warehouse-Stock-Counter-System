<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Daftar — F&B Warehouse System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] font-sans min-h-screen flex">
    {{-- BRANDING PANEL (desktop) --}}
    <div class="hidden lg:flex w-[44%] min-h-screen bg-canvas text-ink flex-col justify-between p-10 relative overflow-hidden page-enter">
        <div class="w-10 h-10 rounded-lg bg-brand flex items-center justify-center font-bold text-xl">F</div>
        <div>
            <h1 class="text-3xl font-bold leading-tight">Daftar Akun<br>Warehouse Staff</h1>
            <div class="mt-8 grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-lg bg-surface p-3"><div class="font-mono text-lg font-bold text-brand">FEFO</div><div class="text-muted text-[10px] label-caps uppercase">First Expired</div></div>
                <div class="rounded-lg bg-surface p-3"><div class="font-mono text-lg font-bold text-brand">Batch</div><div class="text-muted text-[10px] label-caps uppercase">Lot Tracking</div></div>
                <div class="rounded-lg bg-surface p-3"><div class="font-mono text-lg font-bold text-brand">Audit</div><div class="text-muted text-[10px] label-caps uppercase">Immutable Log</div></div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-muted">
            <span class="w-2 h-2 rounded-full bg-green-500 dot-live"></span> Sistem aktif · Gudang Pusat
        </div>
    </div>

    {{-- FORM PANEL --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center gap-2.5 mb-8">
                <div class="w-9 h-9 rounded-lg bg-brand flex items-center justify-center font-bold text-xl text-white">F</div>
                <div class="font-bold leading-tight">F&B Warehouse</div>
            </div>

            <h2 class="text-2xl font-bold">Daftar Akun Baru</h2>

            <form method="POST" action="/register" class="space-y-4">
                @csrf
                <div>
                    <label class="label-caps text-slate-500">Nama Lengkap</label>
                    <input name="name" required placeholder="Nama Anda" class="mt-1 w-full px-3 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="label-caps text-slate-500">Email</label>
                    <input name="email" type="email" required placeholder="nama@perusahaan.id" class="mt-1 w-full px-3 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label-caps text-slate-500">Password</label>
                        <div class="relative">
                            <input name="password" type="password" required minlength="6" placeholder="Min. 6 karakter" class="mt-1 w-full pr-10 px-3 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand">
                            <button type="button" onclick="togglePass(this)" tabindex="-1" title="Lihat password" class="absolute inset-y-0 right-0 px-2.5 text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5 eye-open" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="label-caps text-slate-500">Konfirmasi</label>
                        <div class="relative">
                            <input type="password" placeholder="Ulangi password" class="mt-1 w-full pr-10 px-3 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand">
                            <button type="button" onclick="togglePass(this)" tabindex="-1" title="Lihat password" class="absolute inset-y-0 right-0 px-2.5 text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5 eye-open" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <button class="w-full bg-brand hover:bg-brand-dark text-white font-semibold px-4 py-2.5 rounded-md active:scale-[.99] transition">Buat Akun</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="/login" class="text-brand hover:underline font-medium">Masuk</a></p>
        </div>
    </div>

    <x-toast />
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