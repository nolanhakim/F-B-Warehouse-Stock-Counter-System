@extends('layouts.app')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Log Aktivitas Sistem</h1>
    <div class="flex gap-2 text-sm">
        <input type="date" class="px-3 py-2 rounded-md border border-slate-300">
        <button class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-md">Export</button>
    </div>
</div>

{{-- STAT --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @foreach([
        ['Total Aktivitas',$stats['total'],'semua aktivitas','text-slate-900'],
        ['Aktivitas Hari Ini',$stats['today'],'hari ini','text-green-600'],
        ['Login/Hari Ini',$stats['login_today'],'login hari ini','text-slate-500'],
        ['Perubahan Data',$stats['perubahan'],'create · update · delete','text-slate-500'],
    ] as $s)
    <div class="bg-white border border-slate-200 rounded-lg p-4">
        <div class="label-caps text-slate-500">{{ $s[0] }}</div>
        <div data-pop class="mt-1 font-mono text-2xl font-bold tabular">{{ $s[1] }}</div>
        <div class="text-xs mt-1 {{ $s[3] }}">{{ $s[2] }}</div>
    </div>
    @endforeach
</div>

{{-- FILTER --}}
<div class="bg-white border border-slate-200 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-3 text-sm">
    <input id="searchLog" class="flex-1 min-w-[200px] px-3 py-2 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Cari email / aksi / objek...">
    <select id="filterLogAction" class="px-3 py-2 rounded-md border border-slate-300"><option value="">Semua Aksi</option><option>Create</option><option>Update</option><option>Delete</option></select>
</div>

{{-- TABLE --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50 whitespace-nowrap">
                    <th class="px-4 py-2.5 font-medium">Timestamp</th>
                    <th class="px-3 py-2.5 font-medium">Email</th>
                    <th class="px-3 py-2.5 text-center font-medium">Aksi</th>
                    <th class="px-3 py-2.5 font-medium">Objek</th>
                    <th class="px-3 py-2.5 font-mono font-medium">Sebelum → Sesudah</th>
                    <th class="px-4 py-2.5 font-medium">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($logs as $l)
                <tr data-laction="{{ $l->action }}" data-lsearch="{{ strtolower(($l->email ?? '') . ' ' . $l->action . ' ' . ($l->object ?? '')) }}" class="log-row hover:bg-slate-50 {{ $loop->index % 2 ? 'bg-slate-50/50' : '' }}" style="display:">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500 tabular whitespace-nowrap">{{ $l->created_at->format('d M Y H:i') }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap">{{ $l->email ?? 'Sistem' }}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ in_array($l->action,['Create','Approval']) ? 'bg-green-50 border-green-300 text-green-700' : ($l->action=='Delete' ? 'bg-red-50 border-red-300 text-red-600' : ($l->action=='Logout' ? 'bg-slate-100 border-slate-300 text-slate-600' : 'bg-amber-50 border-amber-300 text-amber-700')) }}">{{ $l->action }}</span>
                    </td>
                    <td class="px-3 py-2.5 font-mono text-xs whitespace-nowrap">{{ $l->object ?? '-' }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs text-slate-600 whitespace-nowrap">{{ $l->description ?? '—' }}</td>
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500 whitespace-nowrap">{{ $l->ip_address ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $logs->links() }}
</div>

<script>
function filterLogs() {
    const q = document.getElementById('searchLog').value.toLowerCase();
    const action = document.getElementById('filterLogAction').value;
    document.querySelectorAll('.log-row').forEach(tr => {
        const matchSearch = !q || tr.dataset.lsearch.includes(q);
        const matchAction = !action || tr.dataset.laction === action;
        tr.style.display = (matchSearch && matchAction) ? '' : 'none';
    });
}
document.getElementById('searchLog').addEventListener('input', filterLogs);
document.getElementById('filterLogAction').addEventListener('change', filterLogs);
</script>
@endsection