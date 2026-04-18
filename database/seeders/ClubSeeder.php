<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Str;

class ClubSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $this->upsertUser('admin@ligaanakpiamanlaweh.local', 'Admin Liga Anak Piaman Laweh', 'admin', 'admin12345');
        $this->upsertUser('garuda@ligaanakpiamanlaweh.local', 'Garuda Muda Manager', 'club', 'club12345');
        $this->upsertUser('elang@ligaanakpiamanlaweh.local', 'Elang Nusantara Manager', 'club', 'club12345');
        $this->upsertUser('rajawali@ligaanakpiamanlaweh.local', 'Rajawali City Manager', 'club', 'club12345');

        $admin = $this->adminUser();

        foreach ([
            [
                'name' => 'Garuda Muda FC',
                'user_email' => 'garuda@ligaanakpiamanlaweh.local',
                'short_name' => 'GMF',
                'manager_name' => 'Rian Pratama',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Jakarta Barat',
                'founded_year' => 2018,
                'address' => 'Jl. Garuda 10, Jakarta Barat',
                'training_address' => 'Lapangan Garuda, Jakarta Barat',
                'notes' => 'Klub demo Garuda Muda FC.',
            ],
            [
                'name' => 'Elang Nusantara',
                'user_email' => 'elang@ligaanakpiamanlaweh.local',
                'short_name' => 'ELANG',
                'manager_name' => 'Dimas Saputra',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Bandung',
                'founded_year' => 2017,
                'address' => 'Jl. Nusantara 21, Bandung',
                'training_address' => 'Lapangan Nusantara, Bandung',
                'notes' => 'Klub demo Elang Nusantara.',
            ],
            [
                'name' => 'Rajawali City',
                'user_email' => 'rajawali@ligaanakpiamanlaweh.local',
                'short_name' => 'RJC',
                'manager_name' => 'Bagas Mahendra',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Surabaya',
                'founded_year' => 2016,
                'address' => 'Jl. Stadion 8, Surabaya',
                'training_address' => 'Lapangan Rajawali, Surabaya',
                'notes' => 'Klub demo Rajawali City.',
            ],
        ] as $club) {
            $user = User::where('email', $club['user_email'])->firstOrFail();
            $logoPath = $this->seedDemoImage(Str::slug($club['short_name']).'-logo.svg', $club['name']);
            $statementPath = $this->seedDemoDocument(Str::slug($club['short_name']).'-statement.pdf', 'Surat Pernyataan '.$club['name']);

            $this->upsertClub(
                ['name' => $club['name']],
                [
                    'user_id' => $user->id,
                    'short_name' => $club['short_name'],
                    'manager_name' => $club['manager_name'],
                    'manager_title' => $club['manager_title'],
                    'zone' => $club['zone'],
                    'founded_year' => $club['founded_year'],
                    'logo_url' => $logoPath,
                    'statement_file_path' => $statementPath,
                    'address' => $club['address'],
                    'training_address' => $club['training_address'],
                    'notes' => $club['notes'],
                    'verification_status' => Club::STATUS_APPROVED,
                    'verification_notes' => 'Data klub demo lengkap dan siap digunakan.',
                    'submitted_at' => now()->subDays(5),
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now()->subDays(3),
                ]
            );
        }
    }
}
