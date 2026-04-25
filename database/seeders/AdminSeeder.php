<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = trim((string) env('ADMIN_SEED_PASSWORD', ''));

        if ($password === '') {
            throw new \RuntimeException('ADMIN_SEED_PASSWORD wajib diisi sebelum menjalankan AdminSeeder.');
        }

        User::updateOrCreate(
            ['email' => 'admin@ligaanakpiamanlaweh.com'],
            [
                'name' => 'Admin Liga Anak Piaman Laweh',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => $password,
                'remember_token' => str()->random(10),
            ]
        );
    }
}
