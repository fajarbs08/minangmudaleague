<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Str;

class PlayerSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $admin = $this->adminUser();

        $rosters = [
            ['club' => 'Garuda Muda FC', 'age_group' => 'U12', 'names' => $this->garudaU12Names()],
            ['club' => 'Garuda Muda FC', 'age_group' => 'U16', 'names' => $this->garudaU16Names()],
            ['club' => 'Elang Nusantara', 'age_group' => 'U12', 'names' => $this->elangU12Names()],
            ['club' => 'Elang Nusantara', 'age_group' => 'U14', 'names' => $this->elangU14Names()],
            ['club' => 'Rajawali City', 'age_group' => 'U14', 'names' => $this->rajawaliU14Names()],
            ['club' => 'Rajawali City', 'age_group' => 'U16', 'names' => $this->rajawaliU16Names()],
        ];

        foreach ($rosters as $roster) {
            $this->seedRosterPlayers(
                $this->getClub($roster['club']),
                $this->getAgeGroup($roster['age_group']),
                $admin,
                $roster['names']
            );
        }
    }

    private function seedRosterPlayers(Club $club, AgeGroup $ageGroup, User $admin, array $names): void
    {
        foreach ($this->playerBlueprint() as $index => $slot) {
            $name = $names[$index] ?? sprintf('%s %s %02d', $club->short_name, $ageGroup->code, $slot['number']);
            $slug = Str::slug($name);

            $this->upsertPlayer($club, $name, [
                'primary_age_group_id' => $ageGroup->id,
                'jersey_number' => $slot['number'],
                'position' => $slot['position'],
                'mother_name' => 'Ibu '.$name,
                'school_name' => 'Sekolah '.$club->short_name.' '.$ageGroup->code,
                'citizenship' => 'WNI',
                'birth_place' => 'Padang',
                'photo_path' => $this->seedDemoImage($slug.'.svg', $name),
                'family_card_file_path' => $this->seedDemoDocument($slug.'-kk.pdf', 'KK '.$name),
                'diploma_file_path' => $this->seedDemoDocument($slug.'-ijazah.pdf', 'Ijazah '.$name),
                'report_file_path' => $this->seedDemoDocument($slug.'-rapor.pdf', 'Rapor '.$name),
                'birth_certificate_file_path' => $this->seedDemoDocument($slug.'-akta.pdf', 'Akta '.$name),
                'birth_date' => now()->subYears($ageGroup->max_age ?? 14)->subDays($slot['number'] * 8)->toDateString(),
                'height_cm' => 130 + $slot['number'],
                'weight_kg' => 28 + $slot['number'],
                'dominant_foot' => $slot['number'] % 2 === 0 ? 'Kiri' : 'Kanan',
                'is_captain' => $slot['number'] === 1,
                'notes' => 'Pemain demo untuk '.$club->short_name.' '.$ageGroup->name,
                'verification_status' => Player::STATUS_APPROVED,
                'verification_notes' => 'Pemain demo sudah diverifikasi.',
                'submitted_at' => now()->subDays(2),
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDay(),
            ]);
        }
    }

    private function playerBlueprint(): array
    {
        return [
            ['number' => 1, 'position' => 'Goalkeeper'],
            ['number' => 2, 'position' => 'Defender'],
            ['number' => 3, 'position' => 'Defender'],
            ['number' => 4, 'position' => 'Defender'],
            ['number' => 5, 'position' => 'Defender'],
            ['number' => 6, 'position' => 'Midfielder'],
            ['number' => 7, 'position' => 'Midfielder'],
            ['number' => 8, 'position' => 'Midfielder'],
            ['number' => 9, 'position' => 'Forward'],
            ['number' => 10, 'position' => 'Forward'],
            ['number' => 11, 'position' => 'Forward'],
            ['number' => 12, 'position' => 'Midfielder'],
            ['number' => 13, 'position' => 'Defender'],
        ];
    }

    private function garudaU12Names(): array
    {
        return [
            'Adit Pratama',
            'Fadhil Alfarizi',
            'Rizky Maulana',
            'Daffa Syahputra',
            'Fikri Hidayat',
            'Alif Ramadhan',
            'Rayhan Akmal',
            'Arka Prakoso',
            'Naufal Nugraha',
            'Rafli Saputra',
            'Satria Wibawa',
            'Kevin Nugroho',
            'Hafiz Ramdani',
        ];
    }

    private function garudaU16Names(): array
    {
        return [
            'Reza Mahendra',
            'Bayu Pradana',
            'Dimas Kurniawan',
            'Ilham Fauzi',
            'Arya Pratama',
            'Tegar Maulana',
            'Yudha Kurnia',
            'Farhan Alamsyah',
            'Nanda Saputra',
            'Rizal Hidayat',
            'Angga Ramadhan',
            'Hafiz Rahman',
            'Fajar Nugraha',
        ];
    }

    private function elangU12Names(): array
    {
        return [
            'Putra Ananda',
            'Rafi Kurnia',
            'Zidan Maulana',
            'Arif Ramadhan',
            'Farel Hidayat',
            'Dimas Nugroho',
            'Bagas Prasetyo',
            'Alvin Putra',
            'Aksa Ramdani',
            'Fariz Saputra',
            'Dito Wahyudi',
            'Raka Alamsyah',
            'Niko Pratama',
        ];
    }

    private function elangU14Names(): array
    {
        return [
            'Fikri Alamsyah',
            'Hanif Ramadhan',
            'Naufal Fahreza',
            'Daffa Prakoso',
            'Rifqi Hidayat',
            'Rehan Maulana',
            'Bagas Wicaksono',
            'Satria Nugroho',
            'Alfan Putra',
            'Zaky Saputra',
            'Yusril Akbar',
            'Fauzan Kurnia',
            'Farrel Pradana',
        ];
    }

    private function rajawaliU14Names(): array
    {
        return [
            'Aditya Pratama',
            'Vino Ramdani',
            'Keenan Maulana',
            'Raka Wibowo',
            'Aryan Nugraha',
            'Thoriq Alamsyah',
            'Bima Saputra',
            'Haikal Ramadhan',
            'Revan Prakoso',
            'Arfan Hidayat',
            'Hilmi Kurniawan',
            'Galih Putra',
            'Davi Nugroho',
        ];
    }

    private function rajawaliU16Names(): array
    {
        return [
            'Naufal Pradana',
            'Aji Ramadhan',
            'Ilham Wicaksono',
            'Fadlan Maulana',
            'Kenzie Putra',
            'Aldo Nugraha',
            'Yudha Prakoso',
            'Rangga Saputra',
            'Dzaky Alamsyah',
            'Arga Hidayat',
            'Farel Kurniawan',
            'Taufik Ramdani',
            'Syahdan Aulia',
        ];
    }
}
