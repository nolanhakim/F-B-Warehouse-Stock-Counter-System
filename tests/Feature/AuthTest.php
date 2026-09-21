<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_pendaftaran_otomatis_sebagai_karyawan(): void
    {
        $res = $this->post(route('register.post'), [
            'name' => 'Test Karyawan',
            'email' => 'karyawan.baru@fnb.id',
            'password' => '12345678',
        ]);

        $res->assertRedirect(route('login'));

        $user = User::where('email', 'karyawan.baru@fnb.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('staff', $user->role);
        $this->assertTrue(Hash::check('12345678', $user->password));
        $this->assertNull(session('role'));
    }

    public function test_login_memakai_role_dari_database(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertEquals('admin', session('role'));
    }

    public function test_login_tanpa_akun_default_karyawan(): void
    {
        $this->post(route('login.post'), [
            'email' => 'tidak.ada@fnb.id',
            'password' => 'salah',
        ])->assertRedirect(route('dashboard'));

        $this->assertEquals('staff', session('role'));
    }

    public function test_logout_flush_session(): void
    {
        $this->withSession(['role' => 'super', 'name' => 'Super Admin'])
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertNull(session('role'));
    }
}