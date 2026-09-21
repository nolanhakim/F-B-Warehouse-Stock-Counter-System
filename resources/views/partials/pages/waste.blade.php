<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold">Waste & Persetujuan</h1>
    @if($canApprove)
    <a href="{{ route('mutasi') }}" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-md">+ Catat Waste</a>
    @else
    <a href="{{ route('mutasi') }}" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-md">+ Ajukan Waste</a>
    @endif
</div>

{{-- APPROVAL QUEUE --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden mb-5">
    <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-semibold">@if($canApprove) Menunggu Persetujuan @else Waste Saya — Menunggu Persetujuan @endif</h2>
        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-mono">{{ $pending->count() }}</span>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($pending as $w)
        <div class="flex flex-wrap items-center gap-3 px-5 py-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs text-slate-500">{{ $w->document_number }}</span>
                    <span class="font-mono text-xs">{{ $w->item->sku ?? '-' }}</span>
                    <span class="font-medium">{{ $w->item->name ?? '-' }}</span>
                    <span class="font-mono text-sm font-semibold text-red-600 tabular">{{ $w->quantity }}</span>
                </div>
                <div class="mt-1 text-xs text-slate-500">
                    Batch {{ $w->batch ?? '-' }} · Rak {{ $w->item->rack ?? '-' }} · oleh {{ $w->pic }} · {{ $w->created_at->format('d M Y H:i') }}
                </div>
            </div>
            @if($canApprove)
            <div class="flex gap-2 shrink-0">
                <form method="POST" action="{{ route('waste.approve', $w) }}">
                    @csrf
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-md bg-green-600 hover:bg-green-700 text-white font-medium">Setujui</button>
                </form>
                <form method="POST" action="{{ route('waste.reject', $w) }}">
                    @csrf
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-md border border-slate-300 hover:bg-slate-50">Tolak</button>
                </form>
            </div>
            @else
            <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-700 border-amber-300">Menunggu</span>
            @endif
        </div>
        @empty
        <div class="px-5 py-10 text-center">
            <p class="text-slate-500 font-medium">Tidak ada waste menunggu persetujuan</p>
        </div>
        @endforelse
    </div>
</div>

{{-- HISTORY --}}
<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold">Riwayat Waste</h2>
            <form method="GET" action="{{ route('waste') }}" class="flex flex-wrap items-end gap-2 text-sm">
                <div>
                    <label class="block text-[10px] text-slate-400 mb-0.5">Status</label>
                    <select name="status" class="px-2 py-1.5 rounded-md border border-slate-300 text-xs">
                        <option value="">Semua</option>
                        @foreach(['approved'=>'Disetujui','pending'=>'Menunggu','rejected'=>'Ditolak'] as $k => $v)
                        <option value="{{ $k }}" {{ ($filters['status'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 mb-0.5">Dari</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="px-2 py-1.5 rounded-md border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 mb-0.5">Sampai</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="px-2 py-1.5 rounded-md border border-slate-300 text-xs">
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-md bg-brand text-white text-xs font-medium">Filter</button>
                <a href="{{ route('waste') }}" class="px-2 py-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs">Reset</a>
                <a href="{{ route('waste.export', request()->query()) }}" class="px-2 py-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs">Export CSV</a>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left label-caps text-slate-500 border-b border-slate-200 bg-slate-50">
                    <th class="px-4 py-2.5 font-medium">Dokumen</th>
                    <th class="px-3 py-2.5 font-medium">SKU</th>
                    <th class="px-3 py-2.5 font-medium">Barang</th>
                    <th class="px-3 py-2.5 text-right font-medium">Qty</th>
                    <th class="px-3 py-2.5 font-medium">Batch</th>
                    <th class="px-3 py-2.5 font-medium">PIC</th>
                    <th class="px-3 py-2.5 text-center font-medium">Waktu</th>
                    <th class="px-4 py-2.5 text-center font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($history as $w)
                @php
                    $st = [
                        'approved' => 'bg-green-50 text-green-700 border-green-300',
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-300',
                        'rejected' => 'bg-red-50 text-red-600 border-red-300',
                    ][$w->status] ?? 'bg-slate-100 text-slate-600 border-slate-300';
                @endphp
                <tr class="hover:bg-slate-50 {{ $loop->index % 2 ? 'bg-slate-50/50' : '' }}">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $w->document_number }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs">{{ $w->item->sku ?? '-' }}</td>
                    <td class="px-3 py-2.5 whitespace-nowrap">{{ $w->item->name ?? '-' }}</td>
                    <td class="px-3 py-2.5 font-mono text-right tabular font-semibold text-red-600">{{ $w->quantity }}</td>
                    <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ $w->batch ?? '-' }}</td>
                    <td class="px-3 py-2.5">{{ $w->pic }}</td>
                    <td class="px-3 py-2.5 text-center font-mono text-xs text-slate-500 tabular">{{ $w->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-semibold {{ $st }}">{{ $w->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-slate-400 text-sm">Belum ada data waste</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $history->links() }}
</div>