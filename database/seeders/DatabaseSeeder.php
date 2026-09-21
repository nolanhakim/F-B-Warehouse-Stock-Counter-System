<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            [
                'name' => 'Dewi Lestari',
                'email' => 'super@fnb.id',
                'role' => 'super',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'admin@fnb.id',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'staff@fnb.id',
                'role' => 'staff',
                'is_active' => true,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], [
                'name' => $u['name'],
                'role' => $u['role'],
                'is_active' => $u['is_active'],
                'password' => bcrypt('12345678'),
            ]);
        }
    }
}