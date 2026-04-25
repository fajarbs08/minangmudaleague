<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Str;

class ClubSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $this->call(AdminSeeder::class);
        $this->upsertUser('garuda@ligaanakpiamanlaweh.com', 'Garuda Muda Manager', 'club', 'club12345');
        $this->upsertUser('elang@ligaanakpiamanlaweh.com', 'Elang Nusantara Manager', 'club', 'club12345');
        $this->upsertUser('rajawali@ligaanakpiamanlaweh.com', 'Rajawali City Manager', 'club', 'club12345');
        $this->upsertUser('harimau@ligaanakpiamanlaweh.com', 'Harimau Selatan Manager', 'club', 'club12345');
        $this->upsertUser('cendrawasih@ligaanakpiamanlaweh.com', 'Cendrawasih United Manager', 'club', 'club12345');
        $this->upsertUser('bintang@ligaanakpiamanlaweh.com', 'Bintang Timur Manager', 'club', 'club12345');
        $this->upsertUser('laskar@ligaanakpiamanlaweh.com', 'Laskar Minang Manager', 'club', 'club12345');
        $this->upsertUser('satria@ligaanakpiamanlaweh.com', 'Satria Padang Manager', 'club', 'club12345');
        $this->upsertUser('mutiara@ligaanakpiamanlaweh.com', 'Mutiara Selatan Manager', 'club', 'club12345');
        $this->upsertUser('singa@ligaanakpiamanlaweh.com', 'Singa Laut Manager', 'club', 'club12345');

        $admin = $this->adminUser();

        foreach ([
            [
                'name' => 'Garuda Muda FC',
                'user_email' => 'garuda@ligaanakpiamanlaweh.com',
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
                'user_email' => 'elang@ligaanakpiamanlaweh.com',
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
                'user_email' => 'rajawali@ligaanakpiamanlaweh.com',
                'short_name' => 'RJC',
                'manager_name' => 'Bagas Mahendra',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Surabaya',
                'founded_year' => 2016,
                'address' => 'Jl. Stadion 8, Surabaya',
                'training_address' => 'Lapangan Rajawali, Surabaya',
                'notes' => 'Klub demo Rajawali City.',
            ],
            [
                'name' => 'Harimau Selatan FC',
                'user_email' => 'harimau@ligaanakpiamanlaweh.com',
                'short_name' => 'HSF',
                'manager_name' => 'Rizky Ananda',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Tangerang Selatan',
                'founded_year' => 2019,
                'address' => 'Jl. Harimau Raya 12, Tangerang Selatan',
                'training_address' => 'Lapangan Harimau Selatan, Tangerang Selatan',
                'notes' => 'Klub demo Harimau Selatan FC.',
            ],
            [
                'name' => 'Cendrawasih United',
                'user_email' => 'cendrawasih@ligaanakpiamanlaweh.com',
                'short_name' => 'CU',
                'manager_name' => 'Fajar Mahendra',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Depok',
                'founded_year' => 2018,
                'address' => 'Jl. Cendrawasih 8, Depok',
                'training_address' => 'Lapangan Cendrawasih, Depok',
                'notes' => 'Klub demo Cendrawasih United.',
            ],
            [
                'name' => 'Bintang Timur FC',
                'user_email' => 'bintang@ligaanakpiamanlaweh.com',
                'short_name' => 'BTF',
                'manager_name' => 'Hendra Saputra',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Bekasi',
                'founded_year' => 2020,
                'address' => 'Jl. Bintang Timur 3, Bekasi',
                'training_address' => 'Lapangan Bintang Timur, Bekasi',
                'notes' => 'Klub demo Bintang Timur FC.',
            ],
            [
                'name' => 'Laskar Minang',
                'user_email' => 'laskar@ligaanakpiamanlaweh.com',
                'short_name' => 'LSM',
                'manager_name' => 'Ilham Prakoso',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Padang Pariaman',
                'founded_year' => 2017,
                'address' => 'Jl. Laskar Minang 5, Padang Pariaman',
                'training_address' => 'Lapangan Laskar Minang, Padang Pariaman',
                'notes' => 'Klub demo Laskar Minang.',
            ],
            [
                'name' => 'Satria Padang',
                'user_email' => 'satria@ligaanakpiamanlaweh.com',
                'short_name' => 'SPD',
                'manager_name' => 'Rudi Hartono',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Padang',
                'founded_year' => 2019,
                'address' => 'Jl. Satria 18, Padang',
                'training_address' => 'Lapangan Satria, Padang',
                'notes' => 'Klub demo Satria Padang.',
            ],
            [
                'name' => 'Mutiara Selatan',
                'user_email' => 'mutiara@ligaanakpiamanlaweh.com',
                'short_name' => 'MST',
                'manager_name' => 'Andi Kurniawan',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Pariaman',
                'founded_year' => 2016,
                'address' => 'Jl. Mutiara 4, Pariaman',
                'training_address' => 'Lapangan Mutiara Selatan, Pariaman',
                'notes' => 'Klub demo Mutiara Selatan.',
            ],
            [
                'name' => 'Singa Laut FC',
                'user_email' => 'singa@ligaanakpiamanlaweh.com',
                'short_name' => 'SLF',
                'manager_name' => 'Yoga Ramadhan',
                'manager_title' => 'Ketua / Penanggung Jawab',
                'zone' => 'Pesisir Selatan',
                'founded_year' => 2021,
                'address' => 'Jl. Singa Laut 1, Pesisir Selatan',
                'training_address' => 'Lapangan Singa Laut, Pesisir Selatan',
                'notes' => 'Klub demo Singa Laut FC.',
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
