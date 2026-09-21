
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Cari Stok / Rak</h1>

    <div class="relative mb-4">
        <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 3a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM0 9.5a9.5 9.5 0 1 1 17 6l4.8 4.8-1.4 1.4-4.8-4.8A9.5 9.5 0 0 1 0 9.5z"/></svg>
        <input class="w-full pl-11 pr-3 py-3 rounded-lg border border-slate-300 text-base focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Scan / ketik SKU, nama, atau kode rak...">
    </div>

    <div class="flex flex-wrap gap-2 mb-5">
        @foreach(['DRY','CHILLER','FROZEN','PACK'] as $z)
        <button class="px-3 py-2 rounded-md border border-slate-300 text-sm font-mono hover:bg-slate-50">{{ $z }}</button>
        @endforeach
    </div>

    <div class="space-y-3">
        @foreach([
            ['MLK-001','Susu UHT Full Cream','Chilled','248 Pack','CHILLER-01-A2','www'],
            ['SPC-018','Lada Hitam Biji','Dry Goods','40 Kg','RAK-DRY-B3','ok'],
            ['FZN-012','Ikan Salmon Fillet','Frozen','75 Pack','FROZEN-01-C2','warn'],
        ] as $s)
        <div class="bg-white border border-slate-200 rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg border border-slate-200 flex items-center justify-center label-caps text-slate-400">IMG</div>
            <div class="min-w-0 flex-1">
                <div class="font-medium">{{ $s[1] }}</div>
                <div class="font-mono text-xs text-slate-500">{{ $s[0] }} · {{ $s[4] }}</div>
                <div class="mt-1"><span class="inline-flex rounded px-2 py-0.5 text-[10px] font-semibold
                    {{ $s[2]=='Chilled' ? 'bg-[#E0F2FE] text-[#0369A1]' : ($s[2]=='Frozen' ? 'bg-[#EDE9FE] text-[#6D28D9]' : 'bg-slate-200 text-slate-700') }}">{{ $s[2] }}</span></div>
            </div>
            <div class="text-right shrink-0">
                <div data-pop class="font-mono text-lg font-bold tabular {{ $s[5]=='warn' ? 'text-amber-600' : 'text-slate-900' }}">{{ $s[3] }}</div>
                <div class="label-caps text-slate-400">In Stock</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
