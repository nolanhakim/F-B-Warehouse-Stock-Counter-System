<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white border border-slate-200 rounded-lg p-10 max-w-md w-full text-center">
            <div class="text-7xl font-bold text-slate-200">403</div>
            <h1 class="text-xl font-bold mt-2">Akses Ditolak</h1>
            <p class="text-sm text-slate-500 mt-1">Anda tidak memiliki izin untuk membuka halaman ini.</p>
            <a href="{{ route('dashboard') }}" class="inline-block mt-5 px-4 py-2 rounded-md bg-[#0284C7] hover:bg-[#0369A1] text-white text-sm font-medium transition">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>