<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:4000',
        ]);

        $msg = trim($data['message']);
        $history = $data['history'] ?? [];
        $ctx = $this->contextSnapshot($msg);
        $key = config('services.gemini.key');

        if (!$key) {
            return response()->json(['reply' => $this->fallbackReply($msg, $ctx) . "\n\n> *Atur GEMINI_API_KEY di .env untuk jawaban AI penuh.*"]);
        }

        $system = "Kamu asisten internal aplikasi F&B Warehouse Management & Stock Counter. Kamu HANYA boleh menjawab topik aplikasi ini.\n"
            . "Topik yang WAJIB: (1) data stok & inventaris live dari konteks (stok, safety stock, rak, batch, expired FEFO, waste, opname, kartu stok, audit log). (2) Panduan pakai fitur, alur kerja, dan hak akses peran di sistem ini. (3) Profil, akun, dan aktivitas login pengguna yang sedang login.\n"
            . "Menu & URL aplikasi: Dashboard (/dashboard), Master Barang (/master), Mutasi Stok (/mutasi), Sesi Stock Opname (/opname), Kartu Stok & Audit (/kartu-stok), Waste & Approval (/waste), Alert & Expired (/alerts), Cari Stok (/cari), Manajemen User (/users, super admin), Log Aktivitas (/logs, super admin).\n"
            . "DILARANG keras menjawab topik di luar aplikasi: pengetahuan umum dunia, berita, cuaca, hiburan, matematika, pemrograman, sejarah, politik, agama, atau hal lain yang tak ada hubungannya dengan sistem gudang ini.\n"
            . "Jika pertanyaan di luar topik: jawab singkat bahwa kamu hanya bisa membantu soal sistem gudang ini, lalu tawarkan bantuan yang tersedia.\n"
            . "Jangan mengarang angka stok — wajib pakai KONTEKS LIVE. Jika data tidak ada, katakan tidak ditemukan. FEFO: batch expired terdekat keluar dulu. Opname: blind-count opsional. Waste: butuh approve Admin/Super.\n"
            . "Jangan tampilkan nama model AI atau detail teknis provider.\n"
            . "Format: markdown ringan, bullet bila daftar. Maks 150 kata.";

        $contents = [];
        foreach (array_slice($history, -10) as $h) {
            $contents[] = ['role' => $h['role'] === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $h['content']]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => "KONTEKS LIVE:\n{$ctx}\n\nPERTANYAAN: {$msg}"]]];

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => $contents,
            'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 2048, 'thinkingConfig' => ['thinkingBudget' => 256]],
        ];

        $tried = [];
        $lastErr = null;

        foreach ($this->modelChain() as $model) {
            $tried[] = $model;

            try {
                $res = Http::timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", $payload);
            } catch (\Throwable $e) {
                $lastErr = 'Gagal hubungi Gemini: ' . $e->getMessage();
                continue;
            }

            if ($res->successful()) {
                $text = trim(collect($res->json('candidates.0.content.parts') ?? [])->pluck('text')->filter()->implode(''));

                if ($text !== '') {
                    return response()->json(['reply' => $text]);
                }

                $lastErr = 'Model ' . $model . ' balas kosong (' . ($res->json('candidates.0.finishReason') ?? 'unknown') . ').';
                continue;
            }

            $status = $res->status();
            $lastErr = "Gemini error {$status}: " . ($res->json('error.message') ?? $res->body());

            // 400/401/403 = config atau key salah, model lain tak akan menolong
            if (in_array($status, [400, 401, 403], true)) {
                break;
            }
        }

        return response()->json(['reply' => $this->fallbackReply($msg, $ctx) . "\n\n> *" . e($lastErr ?? 'Semua model gagal.') . " — dicoba: " . implode(', ', $tried) . "*"], 200);
    }

    /**
     * Model default didahulukan, lalu cadangan berurutan.
     */
    private function modelChain(): array
    {
        $models = [config('services.gemini.model')];
        $models = array_merge($models, config('services.gemini.fallback_models', []));

        return array_values(array_unique(array_filter($models)));
    }

    private function contextSnapshot(string $q): string
    {
        $q = mb_strtolower($q);
        $items = Item::withSum(['mutations' => fn ($qq) => $qq->where('status', 'approved')], 'quantity')
            ->orderBy('name')->get();

        $lines = [];
        $lines[] = "Total SKU: {$items->count()} | Aktif: " . $items->where('is_active', true)->count();

        foreach ($items->take(12) as $it) {
            $stok = (int) ($it->mutations_sum_quantity ?? 0);
            $low = $stok < $it->safety_stock ? ' [LOW]' : '';
            $lines[] = "- {$it->sku} {$it->name} | stok={$stok} | safety={$it->safety_stock} | rak={$it->rack} | kat={$it->category}{$low}";
        }
        if ($items->count() > 12) $lines[] = "- ... +" . ($items->count() - 12) . " SKU lain";

        $low = $items->filter(fn ($i) => ((int)($i->mutations_sum_quantity ?? 0)) < $i->safety_stock)->take(5);
        if ($low->isNotEmpty()) {
            $lines[] = "Low stock: " . $low->map(fn ($i) => $i->sku . '(' . ((int)$i->mutations_sum_quantity) . '<' . $i->safety_stock . ')')->join(', ');
        }

        $pending = Mutation::where('type', 'Waste')->where('status', 'pending')->count();
        $lines[] = "Waste pending approval: {$pending}";

        $alerts = Mutation::with('item')->where('status', 'approved')->whereNotNull('expired_at')->get()
            ->groupBy(fn ($m) => $m->item_id . '|' . $m->batch . '|' . $m->expired_at->format('Y-m-d'))
            ->filter(fn ($g) => $g->sum('quantity') > 0)
            ->map(function ($g) { $m = $g->first(); $d = (int) now()->startOfDay()->diffInDays($m->expired_at->startOfDay(), false); return ['m' => $m, 'd' => $d, 'qty' => $g->sum('quantity')]; })
            ->sortBy('d')->take(6);
        if ($alerts->isNotEmpty()) {
            $lines[] = "Expired FEFO terdekat:";
            foreach ($alerts as $a) {
                $lv = $a['d'] <= 0 ? 'EXPIRED' : ($a['d'] <= 7 ? "H-{$a['d']}" : "H-{$a['d']}");
                $lines[] = "- {$a['m']->item->sku} {$a['m']->item->name} batch={$a['m']->batch} exp={$a['m']->expired_at->format('Y-m-d')} ({$lv}) qty={$a['qty']} rak={$a['m']->item->rack}";
            }
        } else {
            $lines[] = "Tidak ada batch menjelang expired (H-30).";
        }

        if (mb_strlen($q) > 2) {
            $hit = $items->filter(fn ($i) => str_contains(mb_strtolower($i->name . ' ' . $i->sku . ' ' . $i->rack), $q))->take(3);
            if ($hit->isNotEmpty()) {
                $lines[] = "Pencarian '{$q}' cocok: " . $hit->map(fn ($i) => $i->sku . ' ' . $i->name . ' stok=' . (int)$i->mutations_sum_quantity)->join(' | ');
            }
        }

        return implode("\n", $lines);
    }

    private function fallbackReply(string $msg, string $ctx): string
    {
        $m = mb_strtolower($msg);
        if (str_contains($m, 'stok') || str_contains($m, 'rak') || str_contains($m, 'sku')) {
            return "Data live saat ini:\n{$ctx}\n\nTanya SKU/nama spesifik biar aku carikan stok & rak-nya.";
        }
        if (str_contains($m, 'expired') || str_contains($m, 'fefo') || str_contains($m, 'kadaluarsa')) {
            return "FEFO = batch expired terdekat keluar dulu. Cek **Alert & Expired** (/alerts):\n{$ctx}";
        }
        if (str_contains($m, 'waste')) return "Waste dicatat di **Mutasi → Waste**, butuh approve Admin/Super di /waste. Sertakan alasan & foto jika ada.";
        if (str_contains($m, 'opname')) return "Opname: Admin buka sesi di /opname → staff hitung (blind-count opsional) → draft auto-save → finish → admin rekonsiliasi varians.";
        if (str_contains($m, 'inbound') || str_contains($m, 'masuk')) return "Inbound di /mutasi (tipe Inbound): isi PO/surat jalan, qty, batch, expired, rak tujuan.";
        if (str_contains($m, 'outbound') || str_contains($m, 'keluar')) return "Outbound di /mutasi (tipe Outbound): sistem potong stok FEFO otomatis dari batch terdekat.";
        return "Aku bisa bantu:\n- Cek stok/rak/expired FEFO\n- Panduan inbound/outbound/waste/opname\n\nContoh: *\"stok ayam fillet\"*, *\"yang mau expired minggu ini\"*, *\"cara waste\"*.\n\nKonteks:\n{$ctx}";
    }
}
