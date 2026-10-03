<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Mutation;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('mutations');
        Schema::dropIfExists('items');

        Schema::create('items', function ($t) {
            $t->id();
            $t->string('sku')->unique();
            $t->string('name');
            $t->string('category');
            $t->string('unit_buy')->nullable();
            $t->string('unit_count')->nullable();
            $t->string('ratio')->nullable();
            $t->integer('safety_stock')->default(0);
            $t->string('rack')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('mutations', function ($t) {
            $t->id();
            $t->string('document_number')->unique();
            $t->string('type');
            $t->foreignId('item_id')->constrained()->cascadeOnDelete();
            $t->string('batch')->nullable();
            $t->integer('quantity');
            $t->string('rack')->nullable();
            $t->string('pic')->nullable();
            $t->timestamps();
            $t->timestamp('expired_at')->nullable();
            $t->string('status')->default('approved');
        });
    }

    private function seedItem(): Item
    {
        return Item::create([
            'sku' => 'SKU-1', 'name' => 'Ayam Fillet', 'category' => 'Chilled',
            'unit_buy' => 'Karton', 'unit_count' => 'Pack', 'ratio' => 12,
            'safety_stock' => 10, 'rack' => 'CHILLER-01-A2', 'is_active' => true,
        ]);
    }

    public function test_chat_tanpa_api_key_pakai_fallback_dengan_konteks_stok(): void
    {
        config(['services.gemini.key' => null]);
        $this->seedItem();

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'stok menipis apa saja?']);

        $res->assertOk()->assertJsonStructure(['reply']);
        $this->assertStringContainsString('Ayam Fillet', $res['reply']);
    }

    public function test_chat_menghitung_stok_dari_mutasi_approved(): void
    {
        config(['services.gemini.key' => null]);
        $item = $this->seedItem();

        Mutation::create(['document_number' => 'IN-1', 'type' => 'Inbound', 'item_id' => $item->id, 'quantity' => 40, 'status' => 'approved']);
        Mutation::create(['document_number' => 'OUT-1', 'type' => 'Outbound', 'item_id' => $item->id, 'quantity' => -25, 'status' => 'approved']);
        Mutation::create(['document_number' => 'IN-PENDING', 'type' => 'Inbound', 'item_id' => $item->id, 'quantity' => 100, 'status' => 'pending']);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'cek stok ayam']);

        $res->assertOk();
        $this->assertStringContainsString('stok=15', $res['reply']);
        $this->assertStringNotContainsString('stok=115', $res['reply']);
    }

    public function test_chat_mengirim_konteks_dan_histori_ke_gemini(): void
    {
        config(['services.gemini.key' => 'key-test']);
        config(['services.gemini.model' => 'gemini-3.8-flash']);
        $this->seedItem();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Stok '], ['text' => 'ayam fillet 0, di CHILLER-01-A2.']]]]],
            ], 200),
        ]);

        $res = $this->withSession(['role' => 'staff'])->postJson(route('chat'), [
            'message' => 'berapa stok ayam?',
            'history' => [['role' => 'user', 'content' => 'halo'], ['role' => 'assistant', 'content' => 'hai']],
        ]);

        $res->assertOk()->assertJson(['reply' => 'Stok ayam fillet 0, di CHILLER-01-A2.']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $roles = array_column(array_column($body['contents'], 'parts'), 0);

            return str_contains($request->url(), 'gemini-3.8-flash:generateContent')
                && str_contains($request->url(), 'key=key-test')
                && str_contains($body['systemInstruction']['parts'][0]['text'], 'FEFO')
                && str_contains($body['systemInstruction']['parts'][0]['text'], 'DILARANG keras')
                && $body['contents'][0]['role'] === 'user'
                && $body['contents'][1]['role'] === 'model'
                && str_contains($body['contents'][2]['parts'][0]['text'], 'KONTEKS LIVE')
                && str_contains($body['contents'][2]['parts'][0]['text'], 'Ayam Fillet')
                && str_contains($body['contents'][2]['parts'][0]['text'], 'berapa stok ayam?')
                && $roles !== [];
        });
    }

    public function test_gemini_503_lanjut_ke_model_cadangan(): void
    {
        config([
            'services.gemini.key' => 'key-test',
            'services.gemini.model' => 'gemini-3.8-flash',
            'services.gemini.fallback_models' => ['gemini-3.6-flash', 'gemini-3.5-flash'],
        ]);
        $this->seedItem();

        Http::fake([
            '*/models/gemini-3.8-flash:*' => Http::response(['error' => ['message' => 'high demand']], 503),
            '*/models/gemini-3.6-flash:*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Jawaban dari cadangan.']]]]],
            ], 200),
        ]);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'berapa stok ayam?']);

        $res->assertOk()->assertJson(['reply' => 'Jawaban dari cadangan.']);

        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'gemini-3.5-flash'));
    }

public function test_semua_model_gagal_gabung_pesan_error(): void
    {
        config([
            'services.gemini.key' => 'key-test',
            'services.gemini.model' => 'gemini-3.8-flash',
            'services.gemini.fallback_models' => ['gemini-3.6-flash'],
        ]);
        $this->seedItem();

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'high demand']], 503)]);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'bagaimana cara mencatat waste?']);

        $res->assertOk();
        $this->assertStringContainsString('/waste', $res['reply']);
        $this->assertStringContainsString('503', $res['reply']);
        $this->assertStringContainsString('gemini-3.8-flash', $res['reply']);
        $this->assertStringContainsString('gemini-3.6-flash', $res['reply']);
    }

    public function test_key_salah_tidak_mencoba_model_lain(): void
    {
        config([
            'services.gemini.key' => 'salah',
            'services.gemini.model' => 'gemini-3.8-flash',
            'services.gemini.fallback_models' => ['gemini-3.6-flash', 'gemini-3.5-flash'],
        ]);
        $this->seedItem();

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'API key not valid']], 403)]);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'hi']);

        $res->assertOk();
        $this->assertStringContainsString('403', $res['reply']);
        Http::assertSentCount(1);
    }

    public function test_balasan_kosong_lanjut_model_berikutnya(): void
    {
        config([
            'services.gemini.key' => 'key-test',
            'services.gemini.model' => 'gemini-3.8-flash',
            'services.gemini.fallback_models' => ['gemini-3.6-flash'],
        ]);
        $this->seedItem();

        Http::fake([
            '*/models/gemini-3.8-flash:*' => Http::response(['candidates' => [['content' => [], 'finishReason' => 'MAX_TOKENS']]], 200),
            '*/models/gemini-3.6-flash:*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Balasan setelah retry.']]]]],
            ], 200),
        ]);

        $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'hi'])
            ->assertOk()
            ->assertJson(['reply' => 'Balasan setelah retry.']);
    }

    public function test_gemini_kosong_balasan_pakai_fallback(): void
    {
        config(['services.gemini.key' => 'key-test']);
        $this->seedItem();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => [], 'finishReason' => 'MAX_TOKENS']],
            ], 200),
        ]);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'bagaimana cara mencatat waste?']);

        $res->assertOk();
        $this->assertStringContainsString('/waste', $res['reply']);
    }

    public function test_chat_menampilkan_batch_terdekat_expired(): void
    {
        config(['services.gemini.key' => null]);
        $item = $this->seedItem();

        Mutation::create([
            'document_number' => 'IN-EXP', 'type' => 'Inbound', 'item_id' => $item->id, 'batch' => 'B-999',
            'quantity' => 12, 'status' => 'approved', 'expired_at' => now()->addDays(3),
        ]);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'yang expired duluan?']);

        $res->assertOk();
        $this->assertStringContainsString('B-999', $res['reply']);
        $this->assertStringContainsString('H-3', $res['reply']);
    }

    public function test_chat_gagal_koneksi_gemini_tetap_jawab(): void
    {
        config(['services.gemini.key' => 'key-test']);
        $this->seedItem();

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota habis']], 429)]);

        $res = $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'bagaimana cara mencatat waste?']);

        $res->assertOk();
        $this->assertStringContainsString('/waste', $res['reply']);
        $this->assertStringContainsString('Gemini error 429', $res['reply']);
    }

    public function test_pertanyaan_di_luar_topik_tetap_dijaga_dalam_scope(): void
    {
        config(['services.gemini.key' => 'key-test']);
        config(['services.gemini.model' => 'gemini-3.8-flash']);
        $this->seedItem();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Maaf, aku hanya bisa bantu soal sistem gudang ini.']]]]],
            ], 200),
        ]);

        $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'siapa president Indonesia?'])
            ->assertOk();

        Http::assertSent(function ($request) {
            $system = mb_strtolower($request->data()['systemInstruction']['parts'][0]['text']);

            foreach (['berita', 'cuaca', 'matematika', 'pemrograman', 'hanya boleh menjawab topik aplikasi ini'] as $wajib) {
                if (!str_contains($system, $wajib)) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_chat_menolak_pesan_kosong_dan_history_ngawur(): void
    {
        config(['services.gemini.key' => 'key-test']);
        Http::fake();

        $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => ''])
            ->assertStatus(422);

        $this->withSession(['role' => 'staff'])
            ->postJson(route('chat'), ['message' => 'hi', 'history' => [['role' => 'system', 'content' => 'x']]])
            ->assertStatus(422);

        Http::assertNothingSent();
    }
}
