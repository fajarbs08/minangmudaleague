<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Support\Str;

class OfficialSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $admin = $this->adminUser();

        foreach ([
            ['club' => 'Garuda Muda FC', 'age_group' => 'U12', 'name' => 'Rian Pratama', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Garuda Muda FC', 'age_group' => 'U16', 'name' => 'Indra Satria', 'role' => 'Head Coach', 'license_levels' => 'A'],
            ['club' => 'Elang Nusantara', 'age_group' => 'U12', 'name' => 'Dimas Saputra', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Elang Nusantara', 'age_group' => 'U14', 'name' => 'Budi Firmansyah', 'role' => 'Assistant Coach', 'license_levels' => 'C'],
            ['club' => 'Rajawali City', 'age_group' => 'U12', 'name' => 'Dewi Lestari', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Rajawali City', 'age_group' => 'U14', 'name' => 'Bagas Mahendra', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Rajawali City', 'age_group' => 'U16', 'name' => 'Ilham Ramadhan', 'role' => 'Dokter', 'license_levels' => 'Non-Lisensi'],
            ['club' => 'Harimau Selatan FC', 'age_group' => 'U12', 'name' => 'Rizky Ananda', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Cendrawasih United', 'age_group' => 'U12', 'name' => 'Fajar Mahendra', 'role' => 'Head Coach', 'license_levels' => 'A'],
            ['club' => 'Bintang Timur FC', 'age_group' => 'U12', 'name' => 'Hendra Saputra', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Laskar Minang', 'age_group' => 'U12', 'name' => 'Ilham Prakoso', 'role' => 'Assistant Coach', 'license_levels' => 'C'],
            ['club' => 'Satria Padang', 'age_group' => 'U12', 'name' => 'Rudi Hartono', 'role' => 'Manager', 'license_levels' => 'B'],
            ['club' => 'Mutiara Selatan', 'age_group' => 'U12', 'name' => 'Andi Kurniawan', 'role' => 'Head Coach', 'license_levels' => 'B'],
            ['club' => 'Singa Laut FC', 'age_group' => 'U12', 'name' => 'Yoga Ramadhan', 'role' => 'Manager', 'license_levels' => 'Non-Lisensi'],
        ] as $index => $official) {
            $club = $this->getClub($official['club']);
            $ageGroup = $this->getAgeGroup($official['age_group']);
            $slug = Str::slug($club->short_name.'-'.$official['name']);

            $this->upsertOfficial($club, $official['name'], [
                'age_group_id' => $ageGroup->id,
                'role' => $official['role'],
                'phone' => '0812'.str_pad((string) (1000000 + $club->id * 100 + $index), 7, '0', STR_PAD_LEFT),
                'email' => $slug.'@official.demo',
                'birth_place' => 'Kota '.$club->short_name,
                'citizenship' => 'WNI',
                'identity_number' => 'ID'.str_pad((string) ($club->id * 100 + $index + 1), 10, '0', STR_PAD_LEFT),
                'birth_date' => now()->subYears(28 + $index)->subDays(($index + 1) * 9)->toDateString(),
                'license_number' => strtoupper($club->short_name).'-OFF-'.str_pad((string) ($index + 10), 3, '0', STR_PAD_LEFT),
                'license_levels' => $official['license_levels'],
                'photo_path' => $this->seedDemoImage($slug.'.svg', $official['name']),
                'license_file_path' => $this->seedDemoDocument($slug.'-license.pdf', 'Lisensi '.$official['name']),
                'identity_file_path' => $this->seedDemoDocument($slug.'-ktp.pdf', 'KTP '.$official['name']),
                'is_active' => true,
                'notes' => 'Official demo untuk kebutuhan pengujian workflow.',
                'verification_status' => Official::STATUS_APPROVED,
                'verification_notes' => 'Official demo sudah diverifikasi.',
                'submitted_at' => now()->subDays(3),
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDay(),
            ]);
        }
    }
}
