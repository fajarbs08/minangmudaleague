<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\InformationResource;
use App\Models\LineupList;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\OfficialAgeGroup;
use App\Models\Player;
use App\Models\PlayerAgeGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanupLegacyDemoData();

        $admin = $this->upsertUser(
            'admin@ligaanakpiamanlaweh.local',
            'Admin Liga Anak Piaman Laweh',
            'admin',
            'admin12345'
        );

        $clubAUser = $this->upsertUser(
            'garuda@ligaanakpiamanlaweh.local',
            'Garuda Muda Manager',
            'club',
            'club12345'
        );

        $clubBUser = $this->upsertUser(
            'elang@ligaanakpiamanlaweh.local',
            'Elang Nusantara Manager',
            'club',
            'club12345'
        );

        $clubCUser = $this->upsertUser(
            'rajawali@ligaanakpiamanlaweh.local',
            'Rajawali City Manager',
            'club',
            'club12345'
        );

        $u12 = AgeGroup::where('code', 'U12')->firstOrFail();
        $u14 = AgeGroup::where('code', 'U14')->firstOrFail();
        $u16 = AgeGroup::where('code', 'U16')->firstOrFail();
        $garudaLogo = $this->seedDemoImage('garuda-logo.svg', 'Garuda Muda FC');
        $elangLogo = $this->seedDemoImage('elang-logo.svg', 'Elang Nusantara');
        $rajawaliLogo = $this->seedDemoImage('rajawali-logo.svg', 'Rajawali City');
        $garudaStatement = $this->seedDemoDocument('garuda-statement.pdf', 'Surat Pernyataan Garuda Muda FC');
        $elangStatement = $this->seedDemoDocument('elang-statement.pdf', 'Surat Pernyataan Elang Nusantara');
        $rajawaliStatement = $this->seedDemoDocument('rajawali-statement.pdf', 'Surat Pernyataan Rajawali City');

        $garuda = $this->upsertClub([
            'name' => 'Garuda Muda FC',
        ], [
            'user_id' => $clubAUser->id,
            'short_name' => 'GMF',
            'manager_name' => 'Rian Pratama',
            'manager_title' => 'Ketua / Penanggung Jawab',
            'zone' => 'Jakarta Barat',
            'founded_year' => 2018,
            'logo_url' => $garudaLogo,
            'address' => 'Jl. Garuda 10, Jakarta Barat',
            'training_address' => 'Lapangan Garuda, Jakarta Barat',
            'statement_file_path' => $garudaStatement,
            'notes' => 'Klub contoh dengan data yang masih bisa dilengkapi.',
            'verification_status' => Club::STATUS_DRAFT,
            'verification_notes' => null,
            'submitted_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $elang = $this->upsertClub([
            'name' => 'Elang Nusantara',
        ], [
            'user_id' => $clubBUser->id,
            'short_name' => 'ELANG',
            'manager_name' => 'Dimas Saputra',
            'manager_title' => 'Ketua / Penanggung Jawab',
            'zone' => 'Bandung',
            'founded_year' => 2017,
            'logo_url' => $elangLogo,
            'address' => 'Jl. Nusantara 21, Bandung',
            'training_address' => 'Lapangan Nusantara, Bandung',
            'statement_file_path' => $elangStatement,
            'notes' => 'Klub contoh yang sedang menunggu verifikasi admin.',
            'verification_status' => Club::STATUS_SUBMITTED,
            'verification_notes' => null,
            'submitted_at' => now()->subDays(2),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $rajawali = $this->upsertClub([
            'name' => 'Rajawali City',
        ], [
            'user_id' => $clubCUser->id,
            'short_name' => 'RJC',
            'manager_name' => 'Bagas Mahendra',
            'manager_title' => 'Ketua / Penanggung Jawab',
            'zone' => 'Surabaya',
            'founded_year' => 2016,
            'logo_url' => $rajawaliLogo,
            'address' => 'Jl. Stadion 8, Surabaya',
            'training_address' => 'Lapangan Rajawali, Surabaya',
            'statement_file_path' => $rajawaliStatement,
            'notes' => 'Klub contoh yang sudah lolos verifikasi.',
            'verification_status' => Club::STATUS_APPROVED,
            'verification_notes' => 'Data klub lengkap dan memenuhi syarat.',
            'submitted_at' => now()->subDays(7),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(5),
        ]);

        $this->seedOfficials($admin, $garuda, $elang, $rajawali);
        $this->seedPlayers($admin, $u12, $u14, $u16, $garuda, $elang, $rajawali);
        $this->seedLineups($admin, $u12, $u14, $u16, $garuda, $elang, $rajawali);
        $this->ensureDemoValidity($admin, collect([$garuda, $elang, $rajawali]));
        $this->seedInformationResources($admin);
    }

    private function cleanupLegacyDemoData(): void
    {
        Club::where('name', 'Netral United')->delete();

        User::whereIn('email', [
            'admin@minangmuda.local',
            'netral@minangmuda.local',
            'admin@minangmudaleague.local',
            'garuda@minangmudaleague.local',
            'elang@minangmudaleague.local',
            'rajawali@minangmudaleague.local',
        ])->delete();

        if (Schema::hasTable('rule_documents')) {
            DB::table('rule_documents')->delete();
        }
    }

    private function seedOfficials(User $admin, Club $garuda, Club $elang, Club $rajawali): void
    {
        $u12 = AgeGroup::where('code', 'U12')->first();
        $u14 = AgeGroup::where('code', 'U14')->first();
        $u16 = AgeGroup::where('code', 'U16')->first();

        $garudaPhoto = $this->seedDemoImage('official-garuda-rian.svg', 'Rian Pratama');
        $elangCoachPhoto = $this->seedDemoImage('official-elang-dimas.svg', 'Dimas Saputra');
        $elangAssistantPhoto = $this->seedDemoImage('official-elang-budi.svg', 'Budi Firmansyah');
        $rajawaliPhoto = $this->seedDemoImage('official-rajawali-bagas.svg', 'Bagas Mahendra');
        $garudaLicense = $this->seedDemoDocument('official-garuda-rian-license.pdf', 'Lisensi Rian Pratama');
        $elangCoachLicense = $this->seedDemoDocument('official-elang-dimas-license.pdf', 'Lisensi Dimas Saputra');
        $elangAssistantLicense = $this->seedDemoDocument('official-elang-budi-license.pdf', 'Lisensi Budi Firmansyah');
        $rajawaliLicense = $this->seedDemoDocument('official-rajawali-bagas-license.pdf', 'Lisensi Bagas Mahendra');
        $garudaIdentity = $this->seedDemoDocument('official-garuda-rian-ktp.pdf', 'KTP Rian Pratama');
        $elangCoachIdentity = $this->seedDemoDocument('official-elang-dimas-ktp.pdf', 'KTP Dimas Saputra');
        $elangAssistantIdentity = $this->seedDemoDocument('official-elang-budi-ktp.pdf', 'KTP Budi Firmansyah');
        $rajawaliIdentity = $this->seedDemoDocument('official-rajawali-bagas-ktp.pdf', 'KTP Bagas Mahendra');

        $this->upsertOfficial($garuda, 'Rian Pratama', [
            'role' => 'Manager',
            'age_group_id' => $u12?->id,
            'phone' => '081200000101',
            'email' => 'rian@garudamuda.test',
            'birth_date' => '1988-03-12',
            'license_number' => 'MGR-GMF-01',
            'photo_path' => $garudaPhoto,
            'license_file_path' => $garudaLicense,
            'identity_file_path' => $garudaIdentity,
            'is_active' => true,
            'notes' => 'Masih draft dan belum diajukan.',
            'verification_status' => Official::STATUS_DRAFT,
        ]);

        $this->upsertOfficial($elang, 'Dimas Saputra', [
            'role' => 'Head Coach',
            'age_group_id' => $u14?->id,
            'phone' => '081200000201',
            'email' => 'dimas@elang.test',
            'birth_date' => '1985-09-02',
            'license_number' => 'LIC-ELG-02',
            'photo_path' => $elangCoachPhoto,
            'license_file_path' => $elangCoachLicense,
            'identity_file_path' => $elangCoachIdentity,
            'is_active' => true,
            'notes' => 'Menunggu verifikasi admin.',
            'verification_status' => Official::STATUS_SUBMITTED,
            'submitted_at' => now()->subDay(),
        ]);

        $this->upsertOfficial($elang, 'Budi Firmansyah', [
            'role' => 'Assistant Coach',
            'age_group_id' => $u14?->id,
            'phone' => '081200000202',
            'email' => 'budi@elang.test',
            'birth_date' => '1989-01-15',
            'license_number' => 'LIC-ELG-03',
            'photo_path' => $elangAssistantPhoto,
            'license_file_path' => $elangAssistantLicense,
            'identity_file_path' => $elangAssistantIdentity,
            'is_active' => true,
            'notes' => 'Perlu perbaikan lisensi.',
            'verification_status' => Official::STATUS_REVISION,
            'verification_notes' => 'Unggah ulang bukti lisensi dengan file yang lebih jelas.',
            'submitted_at' => now()->subDays(4),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(2),
        ]);

        $this->upsertOfficial($rajawali, 'Bagas Mahendra', [
            'role' => 'Manager',
            'age_group_id' => $u16?->id,
            'phone' => '081200000301',
            'email' => 'bagas@rajawali.test',
            'birth_date' => '1987-07-22',
            'license_number' => 'MGR-RJC-01',
            'photo_path' => $rajawaliPhoto,
            'license_file_path' => $rajawaliLicense,
            'identity_file_path' => $rajawaliIdentity,
            'is_active' => true,
            'notes' => 'Sudah terverifikasi.',
            'verification_status' => Official::STATUS_APPROVED,
            'verification_notes' => 'Dokumen official valid.',
            'submitted_at' => now()->subDays(6),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(5),
        ]);

        $this->seedOfficialBatch($garuda, [
            ['name' => 'Aldi Pranata', 'role' => 'Head Coach', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u12?->id],
            ['name' => 'Rizal Kurnia', 'role' => 'Assistant Coach', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u12?->id],
            ['name' => 'Fahri Mahendra', 'role' => 'Kitman', 'status' => Official::STATUS_SUBMITTED, 'age_group_id' => $u12?->id],
            ['name' => 'Dion Saputra', 'role' => 'Dokter Tim', 'status' => Official::STATUS_REVISION, 'age_group_id' => $u12?->id],
        ], $admin);

        $this->seedOfficialBatch($elang, [
            ['name' => 'Yogi Setiawan', 'role' => 'Manager', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u14?->id],
            ['name' => 'Rama Fadillah', 'role' => 'Goalkeeper Coach', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u14?->id],
            ['name' => 'Fikri Prasetyo', 'role' => 'Fisioterapis', 'status' => Official::STATUS_SUBMITTED, 'age_group_id' => $u14?->id],
            ['name' => 'Ari Wibowo', 'role' => 'Kitman', 'status' => Official::STATUS_REJECTED, 'age_group_id' => $u14?->id],
        ], $admin);

        $this->seedOfficialBatch($rajawali, [
            ['name' => 'Guntur Pradana', 'role' => 'Head Coach', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u16?->id],
            ['name' => 'Dimas Kurniawan', 'role' => 'Assistant Coach', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u16?->id],
            ['name' => 'Ilham Ramadhan', 'role' => 'Dokter Tim', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u16?->id],
            ['name' => 'Aldo Mahesa', 'role' => 'Fisioterapis', 'status' => Official::STATUS_APPROVED, 'age_group_id' => $u16?->id],
        ], $admin);
    }

    private function seedPlayers(User $admin, AgeGroup $u12, AgeGroup $u14, AgeGroup $u16, Club $garuda, Club $elang, Club $rajawali): void
    {
        $aditPhoto = $this->seedDemoImage('player-garuda-adit.svg', 'Adit Nugraha');
        $farhanPhoto = $this->seedDemoImage('player-elang-farhan.svg', 'Farhan Maulana');
        $rafliPhoto = $this->seedDemoImage('player-elang-rafli.svg', 'Rafli Kurniawan');
        $naufalPhoto = $this->seedDemoImage('player-rajawali-naufal.svg', 'Naufal Pradana');

        $this->upsertPlayer($garuda, 'Adit Nugraha', [
            'primary_age_group_id' => $u12->id,
            'jersey_number' => 10,
            'position' => 'Forward',
            'photo_path' => $aditPhoto,
            'family_card_file_path' => $this->seedDemoDocument('player-garuda-adit-kk.pdf', 'KK Adit Nugraha'),
            'diploma_file_path' => $this->seedDemoDocument('player-garuda-adit-ijazah.pdf', 'Ijazah Adit Nugraha'),
            'report_file_path' => $this->seedDemoDocument('player-garuda-adit-rapor.pdf', 'Rapor Adit Nugraha'),
            'birth_certificate_file_path' => $this->seedDemoDocument('player-garuda-adit-akta.pdf', 'Akta Adit Nugraha'),
            'birth_date' => '2014-05-11',
            'height_cm' => 145,
            'weight_kg' => 37,
            'is_captain' => true,
            'notes' => 'Draft player.',
            'verification_status' => Player::STATUS_DRAFT,
        ]);

        $this->upsertPlayer($elang, 'Farhan Maulana', [
            'primary_age_group_id' => $u14->id,
            'jersey_number' => 8,
            'position' => 'Midfielder',
            'photo_path' => $farhanPhoto,
            'family_card_file_path' => $this->seedDemoDocument('player-elang-farhan-kk.pdf', 'KK Farhan Maulana'),
            'diploma_file_path' => $this->seedDemoDocument('player-elang-farhan-ijazah.pdf', 'Ijazah Farhan Maulana'),
            'report_file_path' => $this->seedDemoDocument('player-elang-farhan-rapor.pdf', 'Rapor Farhan Maulana'),
            'birth_certificate_file_path' => $this->seedDemoDocument('player-elang-farhan-akta.pdf', 'Akta Farhan Maulana'),
            'birth_date' => '2012-08-09',
            'height_cm' => 152,
            'weight_kg' => 42,
            'is_captain' => false,
            'notes' => 'Sedang dalam proses review.',
            'verification_status' => Player::STATUS_SUBMITTED,
            'submitted_at' => now()->subDay(),
        ]);

        $this->upsertPlayer($elang, 'Rafli Kurniawan', [
            'primary_age_group_id' => $u14->id,
            'jersey_number' => 14,
            'position' => 'Defender',
            'photo_path' => $rafliPhoto,
            'family_card_file_path' => $this->seedDemoDocument('player-elang-rafli-kk.pdf', 'KK Rafli Kurniawan'),
            'diploma_file_path' => $this->seedDemoDocument('player-elang-rafli-ijazah.pdf', 'Ijazah Rafli Kurniawan'),
            'report_file_path' => $this->seedDemoDocument('player-elang-rafli-rapor.pdf', 'Rapor Rafli Kurniawan'),
            'birth_certificate_file_path' => $this->seedDemoDocument('player-elang-rafli-akta.pdf', 'Akta Rafli Kurniawan'),
            'birth_date' => '2012-03-01',
            'height_cm' => 150,
            'weight_kg' => 44,
            'is_captain' => false,
            'notes' => 'Dokumen kurang lengkap.',
            'verification_status' => Player::STATUS_REJECTED,
            'verification_notes' => 'Akta kelahiran tidak terbaca. Silakan unggah ulang dokumen yang valid.',
            'submitted_at' => now()->subDays(5),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(3),
        ]);

        $this->upsertPlayer($rajawali, 'Naufal Pradana', [
            'primary_age_group_id' => $u16->id,
            'jersey_number' => 7,
            'position' => 'Winger',
            'photo_path' => $naufalPhoto,
            'family_card_file_path' => $this->seedDemoDocument('player-rajawali-naufal-kk.pdf', 'KK Naufal Pradana'),
            'diploma_file_path' => $this->seedDemoDocument('player-rajawali-naufal-ijazah.pdf', 'Ijazah Naufal Pradana'),
            'report_file_path' => $this->seedDemoDocument('player-rajawali-naufal-rapor.pdf', 'Rapor Naufal Pradana'),
            'birth_certificate_file_path' => $this->seedDemoDocument('player-rajawali-naufal-akta.pdf', 'Akta Naufal Pradana'),
            'birth_date' => '2010-07-16',
            'height_cm' => 164,
            'weight_kg' => 51,
            'is_captain' => false,
            'notes' => 'Sudah diterima.',
            'verification_status' => Player::STATUS_APPROVED,
            'verification_notes' => 'Data pemain sesuai dokumen.',
            'submitted_at' => now()->subDays(7),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(6),
        ]);

        $this->seedPlayerBatch($garuda, $u12, [
            ['name' => 'Bima Prakoso', 'number' => 1, 'position' => 'Goalkeeper', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Raka Saputra', 'number' => 2, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Fikri Ramadhan', 'number' => 3, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Nabil Hidayat', 'number' => 4, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rifqi Maulana', 'number' => 5, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Daffa Alfarizi', 'number' => 6, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Alif Ramdani', 'number' => 7, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rasyid Akbar', 'number' => 8, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Farel Saputro', 'number' => 9, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Iqbal Firmansyah', 'number' => 11, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rizki Ananda', 'number' => 12, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Satria Mahesa', 'number' => 13, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Azzam Kurnia', 'number' => 14, 'position' => 'Forward', 'status' => Player::STATUS_SUBMITTED],
            ['name' => 'Hanif Prasetyo', 'number' => 15, 'position' => 'Goalkeeper', 'status' => Player::STATUS_REVISION],
        ]);

        $this->seedPlayerBatch($elang, $u14, [
            ['name' => 'Yoga Pratama', 'number' => 1, 'position' => 'Goalkeeper', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Gilang Ramadhan', 'number' => 2, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rendi Saputra', 'number' => 3, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Aldy Nugroho', 'number' => 4, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rehan Alamsyah', 'number' => 5, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Fauzan Ilham', 'number' => 6, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Haikal Fadillah', 'number' => 7, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Bagas Praditya', 'number' => 10, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Raka Maulana', 'number' => 16, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Syauqi Ramadhan', 'number' => 17, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Mikael Putra', 'number' => 18, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Ares Wibowo', 'number' => 19, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Raihan Akmal', 'number' => 20, 'position' => 'Forward', 'status' => Player::STATUS_SUBMITTED],
            ['name' => 'Danish Purnama', 'number' => 21, 'position' => 'Goalkeeper', 'status' => Player::STATUS_REVISION],
        ]);

        $this->seedPlayerBatch($rajawali, $u16, [
            ['name' => 'Abimanyu Putra', 'number' => 1, 'position' => 'Goalkeeper', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rama Satrio', 'number' => 2, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Fadlan Nur', 'number' => 3, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Yusuf Kamil', 'number' => 4, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rangga Akbar', 'number' => 5, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Akbar Maulana', 'number' => 6, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Rifqi Aditya', 'number' => 8, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Aqil Fadhil', 'number' => 9, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Dzaky Pramudita', 'number' => 10, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Keenan Ramadhan', 'number' => 11, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Arta Wijaya', 'number' => 12, 'position' => 'Midfielder', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Milan Prakoso', 'number' => 13, 'position' => 'Defender', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Ravin Prasetya', 'number' => 14, 'position' => 'Forward', 'status' => Player::STATUS_APPROVED],
            ['name' => 'Zidan Rahman', 'number' => 15, 'position' => 'Goalkeeper', 'status' => Player::STATUS_APPROVED],
        ]);
    }

    private function seedLineups(User $admin, AgeGroup $u12, AgeGroup $u14, AgeGroup $u16, Club $garuda, Club $elang, Club $rajawali): void
    {
        $garudaMatchOne = $this->upsertMatchSchedule([
            'age_group_id' => $u12->id,
            'club_a_id' => $garuda->id,
            'club_b_id' => $elang->id,
            'match_day' => 'Matchday 1',
        ], [
            'venue' => 'Stadion Garuda',
            'match_date' => now()->addDays(5)->toDateString(),
            'kickoff_time' => '15:00',
        ]);

        $elangMatchTwo = $this->upsertMatchSchedule([
            'age_group_id' => $u14->id,
            'club_a_id' => $elang->id,
            'club_b_id' => $rajawali->id,
            'match_day' => 'Matchday 2',
        ], [
            'venue' => 'Lapangan Nusantara',
            'match_date' => now()->addDays(7)->toDateString(),
            'kickoff_time' => '13:00',
        ]);

        $elangMatchThree = $this->upsertMatchSchedule([
            'age_group_id' => $u14->id,
            'club_a_id' => $elang->id,
            'club_b_id' => $garuda->id,
            'match_day' => 'Matchday 3',
        ], [
            'venue' => 'Lapangan Nusantara',
            'match_date' => now()->addDays(14)->toDateString(),
            'kickoff_time' => '09:00',
        ]);

        $rajawaliMatchOne = $this->upsertMatchSchedule([
            'age_group_id' => $u16->id,
            'club_a_id' => $rajawali->id,
            'club_b_id' => $garuda->id,
            'match_day' => 'Matchday 1',
        ], [
            'venue' => 'Rajawali Arena',
            'match_date' => now()->addDays(4)->toDateString(),
            'kickoff_time' => '16:00',
        ]);

        $garudaMatchTwo = $this->upsertMatchSchedule([
            'age_group_id' => $u12->id,
            'club_a_id' => $garuda->id,
            'club_b_id' => $rajawali->id,
            'match_day' => 'Matchday 2',
        ], [
            'venue' => 'Stadion Garuda',
            'match_date' => now()->addDays(12)->toDateString(),
            'kickoff_time' => '10:00',
        ]);

        $elangMatchFour = $this->upsertMatchSchedule([
            'age_group_id' => $u14->id,
            'club_a_id' => $elang->id,
            'club_b_id' => $rajawali->id,
            'match_day' => 'Matchday 4',
        ], [
            'venue' => 'Lapangan Nusantara',
            'match_date' => now()->addDays(21)->toDateString(),
            'kickoff_time' => '14:30',
        ]);

        $rajawaliMatchTwo = $this->upsertMatchSchedule([
            'age_group_id' => $u16->id,
            'club_a_id' => $rajawali->id,
            'club_b_id' => $elang->id,
            'match_day' => 'Matchday 2',
        ], [
            'venue' => 'Rajawali Arena',
            'match_date' => now()->addDays(11)->toDateString(),
            'kickoff_time' => '08:30',
        ]);

        $garudaMatchThree = $this->upsertMatchSchedule([
            'age_group_id' => $u12->id,
            'club_a_id' => $garuda->id,
            'club_b_id' => $elang->id,
            'match_day' => 'Matchday 3',
        ], [
            'venue' => 'Stadion Garuda',
            'match_date' => now()->addDays(19)->toDateString(),
            'kickoff_time' => '11:00',
        ]);

        $elangMatchFive = $this->upsertMatchSchedule([
            'age_group_id' => $u14->id,
            'club_a_id' => $elang->id,
            'club_b_id' => $garuda->id,
            'match_day' => 'Matchday 5',
        ], [
            'venue' => 'Lapangan Nusantara',
            'match_date' => now()->addDays(28)->toDateString(),
            'kickoff_time' => '15:30',
        ]);

        $rajawaliMatchThree = $this->upsertMatchSchedule([
            'age_group_id' => $u16->id,
            'club_a_id' => $rajawali->id,
            'club_b_id' => $elang->id,
            'match_day' => 'Matchday 3',
        ], [
            'venue' => 'Rajawali Arena',
            'match_date' => now()->addDays(18)->toDateString(),
            'kickoff_time' => '17:00',
        ]);

        $garudaLineup = $this->upsertLineup($garuda, 'DSP Garuda Muda U12 Pekan 1', [
            'match_id' => $garudaMatchOne->id,
            'age_group_id' => $u12->id,
            'match_day' => 'Matchday 1',
            'match_date' => now()->addDays(5)->toDateString(),
            'coach_name' => 'Rian Pratama',
            'notes' => 'Belum diajukan.',
            'verification_status' => LineupList::STATUS_DRAFT,
        ]);

        $elangLineup = $this->upsertLineup($elang, 'DSP Elang Nusantara U14 Pekan 2', [
            'match_id' => $elangMatchTwo->id,
            'age_group_id' => $u14->id,
            'match_day' => 'Matchday 2',
            'match_date' => now()->addDays(7)->toDateString(),
            'coach_name' => 'Dimas Saputra',
            'notes' => 'Menunggu review admin.',
            'verification_status' => LineupList::STATUS_SUBMITTED,
            'submitted_at' => now()->subHours(20),
        ]);

        $elangRevisionLineup = $this->upsertLineup($elang, 'DSP Elang Nusantara U14 Pekan 3', [
            'match_id' => $elangMatchThree->id,
            'age_group_id' => $u14->id,
            'match_day' => 'Matchday 3',
            'match_date' => now()->addDays(14)->toDateString(),
            'coach_name' => 'Dimas Saputra',
            'notes' => 'Butuh revisi susunan pemain.',
            'verification_status' => LineupList::STATUS_REVISION,
            'verification_notes' => 'Nomor punggung starter belum lengkap. Silakan perbarui DSP.',
            'submitted_at' => now()->subDays(3),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(2),
        ]);

        $rajawaliLineup = $this->upsertLineup($rajawali, 'DSP Rajawali City U16 Pekan 1', [
            'match_id' => $rajawaliMatchOne->id,
            'age_group_id' => $u16->id,
            'match_day' => 'Matchday 1',
            'match_date' => now()->addDays(4)->toDateString(),
            'coach_name' => 'Bagas Mahendra',
            'notes' => 'DSP diterima.',
            'verification_status' => LineupList::STATUS_APPROVED,
            'verification_notes' => 'DSP valid dan siap digunakan.',
            'submitted_at' => now()->subDays(6),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(5),
        ]);

        $garudaSecondLineup = $this->upsertLineup($garuda, 'DSP Garuda Muda U12 Pekan 2', [
            'match_id' => $garudaMatchTwo->id,
            'age_group_id' => $u12->id,
            'match_day' => 'Matchday 2',
            'match_date' => now()->addDays(12)->toDateString(),
            'coach_name' => 'Aldi Pranata',
            'notes' => 'DSP demo untuk simulasi matchday kedua.',
            'verification_status' => LineupList::STATUS_SUBMITTED,
            'submitted_at' => now()->subHours(10),
        ]);

        $elangApprovedLineup = $this->upsertLineup($elang, 'DSP Elang Nusantara U14 Pekan 4', [
            'match_id' => $elangMatchFour->id,
            'age_group_id' => $u14->id,
            'match_day' => 'Matchday 4',
            'match_date' => now()->addDays(21)->toDateString(),
            'coach_name' => 'Dimas Saputra',
            'notes' => 'DSP demo yang sudah disetujui.',
            'verification_status' => LineupList::STATUS_APPROVED,
            'verification_notes' => 'Roster DSP lengkap dan valid.',
            'submitted_at' => now()->subDays(4),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(3),
        ]);

        $rajawaliSecondLineup = $this->upsertLineup($rajawali, 'DSP Rajawali City U16 Pekan 2', [
            'match_id' => $rajawaliMatchTwo->id,
            'age_group_id' => $u16->id,
            'match_day' => 'Matchday 2',
            'match_date' => now()->addDays(11)->toDateString(),
            'coach_name' => 'Guntur Pradana',
            'notes' => 'DSP demo cadangan untuk simulasi cetak kedua.',
            'verification_status' => LineupList::STATUS_DRAFT,
        ]);

        $garudaRejectedLineup = $this->upsertLineup($garuda, 'DSP Garuda Muda U12 Pekan 3', [
            'match_id' => $garudaMatchThree->id,
            'age_group_id' => $u12->id,
            'match_day' => 'Matchday 3',
            'match_date' => now()->addDays(19)->toDateString(),
            'coach_name' => 'Rian Pratama',
            'notes' => 'Contoh DSP yang sempat ditolak admin.',
            'verification_status' => LineupList::STATUS_REJECTED,
            'verification_notes' => 'Roster tidak sesuai karena ada pemain belum valid dan susunan cadangan melebihi kebutuhan.',
            'submitted_at' => now()->subDays(2),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDay(),
        ]);

        $elangDraftLineup = $this->upsertLineup($elang, 'DSP Elang Nusantara U14 Pekan 5', [
            'match_id' => $elangMatchFive->id,
            'age_group_id' => $u14->id,
            'match_day' => 'Matchday 5',
            'match_date' => now()->addDays(28)->toDateString(),
            'coach_name' => 'Yogi Setiawan',
            'notes' => 'Draft lineup untuk simulasi penyusunan awal oleh club.',
            'verification_status' => LineupList::STATUS_DRAFT,
        ]);

        $rajawaliSubmittedLineup = $this->upsertLineup($rajawali, 'DSP Rajawali City U16 Pekan 3', [
            'match_id' => $rajawaliMatchThree->id,
            'age_group_id' => $u16->id,
            'match_day' => 'Matchday 3',
            'match_date' => now()->addDays(18)->toDateString(),
            'coach_name' => 'Bagas Mahendra',
            'notes' => 'DSP demo tambahan yang sedang menunggu review admin.',
            'verification_status' => LineupList::STATUS_SUBMITTED,
            'submitted_at' => now()->subHours(14),
        ]);

        $this->syncLineupPlayers($garudaLineup, [
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Bima Prakoso')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Raka Saputra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Fikri Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Nabil Hidayat')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rifqi Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Daffa Alfarizi')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Alif Ramdani')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rasyid Akbar')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Adit Nugraha')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Farel Saputro')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Iqbal Firmansyah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rizki Ananda')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Satria Mahesa')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($elangLineup, [
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Farhan Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Yoga Pratama')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Gilang Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rendi Saputra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Aldy Nugroho')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rehan Alamsyah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Fauzan Ilham')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Haikal Fadillah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Bagas Praditya')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Raka Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Syauqi Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Mikael Putra')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Ares Wibowo')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rafli Kurniawan')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($elangRevisionLineup, [
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Farhan Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
        ]);

        $this->syncLineupPlayers($rajawaliLineup, [
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Abimanyu Putra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rama Satrio')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Fadlan Nur')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Yusuf Kamil')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rangga Akbar')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Akbar Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rifqi Aditya')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Aqil Fadhil')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Dzaky Pramudita')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Keenan Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Naufal Pradana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Arta Wijaya')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Milan Prakoso')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Ravin Prasetya')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Zidan Rahman')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($garudaSecondLineup, [
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Bima Prakoso')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Raka Saputra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Fikri Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Nabil Hidayat')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rifqi Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Daffa Alfarizi')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Alif Ramdani')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rasyid Akbar')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Farel Saputro')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Iqbal Firmansyah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rizki Ananda')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Satria Mahesa')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Adit Nugraha')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($elangApprovedLineup, [
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Yoga Pratama')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Gilang Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rendi Saputra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Aldy Nugroho')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rehan Alamsyah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Fauzan Ilham')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Haikal Fadillah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Farhan Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Bagas Praditya')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Raka Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Syauqi Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Mikael Putra')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Ares Wibowo')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($rajawaliSecondLineup, [
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Abimanyu Putra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rama Satrio')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Fadlan Nur')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Yusuf Kamil')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rangga Akbar')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Akbar Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rifqi Aditya')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Aqil Fadhil')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Dzaky Pramudita')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Keenan Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Naufal Pradana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Arta Wijaya')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Milan Prakoso')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Ravin Prasetya')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Zidan Rahman')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($garudaRejectedLineup, [
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Bima Prakoso')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Raka Saputra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Fikri Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Nabil Hidayat')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rifqi Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Daffa Alfarizi')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Alif Ramdani')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rasyid Akbar')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Adit Nugraha')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Farel Saputro')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Azzam Kurnia')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Iqbal Firmansyah')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Rizki Ananda')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Satria Mahesa')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $garuda->id)->where('name', 'Hanif Prasetyo')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($elangDraftLineup, [
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Yoga Pratama')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Gilang Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rendi Saputra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Aldy Nugroho')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Rehan Alamsyah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Fauzan Ilham')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Haikal Fadillah')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Bagas Praditya')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Raka Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Syauqi Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Farhan Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Mikael Putra')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Ares Wibowo')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $elang->id)->where('name', 'Raihan Akmal')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);

        $this->syncLineupPlayers($rajawaliSubmittedLineup, [
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Abimanyu Putra')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rama Satrio')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Fadlan Nur')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Yusuf Kamil')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rangga Akbar')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Akbar Maulana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Rifqi Aditya')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Aqil Fadhil')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Dzaky Pramudita')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Keenan Ramadhan')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Naufal Pradana')->first(), 'role' => LineupList::ROLE_STARTER],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Arta Wijaya')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Milan Prakoso')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Ravin Prasetya')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
            ['player' => Player::where('club_id', $rajawali->id)->where('name', 'Zidan Rahman')->first(), 'role' => LineupList::ROLE_SUBSTITUTE],
        ]);
    }

    private function seedInformationResources(User $admin): void
    {
        $items = [
            [
                'title' => 'Panduan Dashboard Club',
                'category' => 'manual',
                'description' => 'Tangkapan layar annotated untuk membantu admin dan klub memahami halaman dashboard utama.',
                'source_path' => public_path('workflow-screens/dashboard-annotated.png'),
                'visibility' => InformationResource::VISIBILITY_PUBLIC,
                'sort_order' => 1,
                'is_pinned' => true,
                'is_published' => true,
            ],
            [
                'title' => 'Panduan Registrasi Official',
                'category' => 'manual',
                'description' => 'Panduan visual untuk pengisian data official dan alur unggah dokumen verifikasi.',
                'source_path' => public_path('workflow-screens/official-create-annotated.png'),
                'visibility' => InformationResource::VISIBILITY_CLUB,
                'sort_order' => 2,
                'is_pinned' => true,
                'is_published' => true,
            ],
            [
                'title' => 'Regulasi Pendaftaran Liga',
                'category' => 'rules',
                'description' => 'Ringkasan ketentuan administrasi pendaftaran klub, official, pemain, dan jadwal verifikasi.',
                'source_path' => storage_path('app/public/demo-documents/liga-topskor-rules.pdf'),
                'visibility' => InformationResource::VISIBILITY_PUBLIC,
                'sort_order' => 3,
                'is_pinned' => false,
                'is_published' => true,
            ],
            [
                'title' => 'Template Surat Pernyataan Klub',
                'category' => 'template',
                'description' => 'Template surat pernyataan yang dapat diunduh lalu disesuaikan oleh masing-masing klub peserta.',
                'source_path' => public_path('documents/surat_pernyataan_liga_piaman_laweh_final.pdf'),
                'visibility' => InformationResource::VISIBILITY_CLUB,
                'sort_order' => 4,
                'is_pinned' => false,
                'is_published' => true,
            ],
            [
                'title' => 'Alur Verifikasi Dokumen',
                'category' => 'flow',
                'description' => 'Diagram alur proses verifikasi dokumen dari draft sampai disetujui admin.',
                'source_path' => public_path('workflow-screens/submit-annotated.png'),
                'visibility' => InformationResource::VISIBILITY_PUBLIC,
                'sort_order' => 5,
                'is_pinned' => false,
                'is_published' => true,
            ],
            [
                'title' => 'Contoh DSP Garuda Muda U12',
                'category' => 'other',
                'description' => 'Contoh berkas DSP yang sudah tersedia di sistem untuk kebutuhan tampilan dan unduhan.',
                'source_path' => storage_path('app/public/demo-documents/garuda-u12-pekan-1.pdf'),
                'visibility' => InformationResource::VISIBILITY_CLUB,
                'sort_order' => 6,
                'is_pinned' => false,
                'is_published' => true,
            ],
            [
                'title' => 'Surat Pernyataan Garuda Muda FC',
                'category' => 'template',
                'description' => 'Contoh surat pernyataan klub yang sudah tersedia di storage aplikasi.',
                'source_path' => storage_path('app/public/demo-documents/garuda-statement.pdf'),
                'visibility' => InformationResource::VISIBILITY_CLUB,
                'sort_order' => 7,
                'is_pinned' => false,
                'is_published' => true,
            ],
            [
                'title' => 'Akta Pendirian Elang Nusantara',
                'category' => 'other',
                'description' => 'Contoh dokumen legal klub untuk pengujian pusat informasi.',
                'source_path' => storage_path('app/public/demo-documents/elang-deed.pdf'),
                'visibility' => InformationResource::VISIBILITY_CLUB,
                'sort_order' => 8,
                'is_pinned' => false,
                'is_published' => false,
            ],
        ];

        foreach ($items as $item) {
            $asset = $this->seedInformationResourceAsset(
                $item['source_path'],
                $item['title']
            );

            $this->upsertInformationResource(
                ['title' => $item['title']],
                [
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'file_path' => $asset['file_path'],
                    'file_name' => $asset['file_name'],
                    'file_mime' => $asset['file_mime'],
                    'visibility' => $item['visibility'],
                    'sort_order' => $item['sort_order'],
                    'is_pinned' => $item['is_pinned'],
                    'is_published' => $item['is_published'],
                    'created_by' => $admin->id,
                ]
            );
        }
    }

    private function ensureDemoValidity(User $admin, \Illuminate\Support\Collection $clubs): void
    {
        $approvedAt = now()->subDays(2);

        foreach ($clubs as $club) {
            $club->forceFill([
                'verification_status' => Club::STATUS_APPROVED,
                'verification_notes' => 'Data klub demo valid dan siap dipakai untuk pengujian.',
                'submitted_at' => $club->submitted_at ?: now()->subDays(5),
                'reviewed_by' => $admin->id,
                'reviewed_at' => $approvedAt,
                'notes' => 'Data klub demo valid untuk pengujian fitur kompetisi.',
            ])->save();
        }

        Official::query()->update([
            'verification_status' => Official::STATUS_APPROVED,
            'verification_notes' => 'Official demo valid dan sudah diverifikasi.',
            'submitted_at' => now()->subDays(4),
            'reviewed_by' => $admin->id,
            'reviewed_at' => $approvedAt,
            'notes' => 'Official demo valid untuk pengujian.',
        ]);

        OfficialAgeGroup::query()->update([
            'registration_status' => Official::STATUS_APPROVED,
            'status_date' => $approvedAt,
        ]);

        Player::query()->update([
            'verification_status' => Player::STATUS_APPROVED,
            'verification_notes' => 'Pemain demo valid dan sudah diverifikasi.',
            'submitted_at' => now()->subDays(4),
            'reviewed_at' => $approvedAt,
            'notes' => 'Pemain demo valid untuk roster kompetisi.',
        ]);

        PlayerAgeGroup::query()->update([
            'registration_status' => Player::STATUS_APPROVED,
            'status_date' => $approvedAt,
        ]);

        $lineups = LineupList::query()->with(['club', 'ageGroup'])->get();

        foreach ($lineups as $lineup) {
            $lineup->forceFill([
                'verification_status' => LineupList::STATUS_APPROVED,
                'verification_notes' => 'DSP demo valid dan siap digunakan.',
                'submitted_at' => now()->subDays(3),
                'reviewed_by' => $admin->id,
                'reviewed_at' => $approvedAt,
                'notes' => 'DSP demo valid untuk pengujian.',
            ])->save();

            $this->syncLineupPlayers($lineup, $this->buildValidLineupEntries($lineup->club_id, $lineup->age_group_id));
        }

        InformationResource::query()->update([
            'is_published' => true,
        ]);

        $this->seedFinishedMatchesAndGoals();
    }

    private function buildValidLineupEntries(int $clubId, ?int $ageGroupId): array
    {
        $players = Player::query()
            ->where('club_id', $clubId)
            ->where('primary_age_group_id', $ageGroupId)
            ->where('verification_status', Player::STATUS_APPROVED)
            ->orderBy('jersey_number')
            ->get();

        $entries = [];

        foreach ($players->take(LineupList::REQUIRED_STARTERS) as $player) {
            $entries[] = [
                'player' => $player,
                'role' => LineupList::ROLE_STARTER,
            ];
        }

        foreach ($players->slice(LineupList::REQUIRED_STARTERS, LineupList::MAX_SUBSTITUTES) as $player) {
            $entries[] = [
                'player' => $player,
                'role' => LineupList::ROLE_SUBSTITUTE,
            ];
        }

        return $entries;
    }

    private function seedFinishedMatchesAndGoals(): void
    {
        MatchGoal::query()->delete();

        $matches = MatchSchedule::query()
            ->with(['clubA', 'clubB', 'ageGroup'])
            ->orderBy('age_group_id')
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->get()
            ->values();

        $resultPlans = [
            [
                'match_index' => 0,
                'date' => now()->subDays(12)->toDateString(),
                'score_a' => 2,
                'score_b' => 1,
                'goals' => [
                    ['club_short' => 'GMF', 'scorer' => 'Adit Nugraha', 'assist' => 'Alif Ramdani'],
                    ['club_short' => 'GMF', 'scorer' => 'Farel Saputro', 'assist' => 'Rasyid Akbar'],
                    ['club_short' => 'ELANG', 'scorer' => 'Farhan Maulana', 'assist' => 'Bagas Praditya'],
                ],
            ],
            [
                'match_index' => 1,
                'date' => now()->subDays(9)->toDateString(),
                'score_a' => 1,
                'score_b' => 1,
                'goals' => [
                    ['club_short' => 'ELANG', 'scorer' => 'Farhan Maulana', 'assist' => 'Fauzan Ilham'],
                    ['club_short' => 'RJC', 'scorer' => 'Naufal Pradana', 'assist' => 'Keenan Ramadhan'],
                ],
            ],
            [
                'match_index' => 3,
                'date' => now()->subDays(6)->toDateString(),
                'score_a' => 3,
                'score_b' => 1,
                'goals' => [
                    ['club_short' => 'RJC', 'scorer' => 'Naufal Pradana', 'assist' => 'Aqil Fadhil'],
                    ['club_short' => 'RJC', 'scorer' => 'Keenan Ramadhan', 'assist' => 'Dzaky Pramudita'],
                    ['club_short' => 'GMF', 'scorer' => 'Adit Nugraha', 'assist' => 'Farel Saputro'],
                    ['club_short' => 'RJC', 'scorer' => 'Ravin Prasetya', 'assist' => null],
                ],
            ],
        ];

        foreach ($matches as $index => $match) {
            $plan = collect($resultPlans)->firstWhere('match_index', $index);

            if ($plan) {
                $match->forceFill([
                    'competition_format' => MatchSchedule::FORMAT_LEAGUE,
                    'round_label' => null,
                    'round_order' => null,
                    'bracket_slot' => null,
                    'match_date' => $plan['date'],
                    'score_club_a' => $plan['score_a'],
                    'score_club_b' => $plan['score_b'],
                    'is_finished' => true,
                ])->save();

                foreach ($plan['goals'] as $order => $goal) {
                    $club = collect([$match->clubA, $match->clubB])
                        ->first(fn (?Club $club) => $club && $club->short_name === $goal['club_short']);

                    $scorer = Player::query()
                        ->where('club_id', $club?->id)
                        ->where('name', $goal['scorer'])
                        ->first();

                    $assist = filled($goal['assist'])
                        ? Player::query()
                            ->where('club_id', $club?->id)
                            ->where('name', $goal['assist'])
                            ->first()
                        : null;

                    if (!$club || !$scorer) {
                        continue;
                    }

                    MatchGoal::create([
                        'match_id' => $match->id,
                        'club_id' => $club->id,
                        'player_id' => $scorer->id,
                        'assist_player_id' => $assist?->id,
                        'display_order' => $order + 1,
                    ]);
                }

                continue;
            }

            $match->forceFill([
                'competition_format' => MatchSchedule::FORMAT_LEAGUE,
                'round_label' => null,
                'round_order' => null,
                'bracket_slot' => null,
                'score_club_a' => null,
                'score_club_b' => null,
                'is_finished' => false,
            ])->save();
        }
    }

    private function upsertUser(string $email, string $name, string $role, string $password): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'remember_token' => Str::random(10),
            ]
        );
    }

    private function upsertClub(array $identity, array $attributes): Club
    {
        return Club::updateOrCreate($identity, $attributes);
    }

    private function upsertOfficial(Club $club, string $name, array $attributes): Official
    {
        $official = Official::updateOrCreate(
            ['club_id' => $club->id, 'name' => $name],
            $attributes + ['club_id' => $club->id]
        );

        if (!empty($attributes['age_group_id'])) {
            OfficialAgeGroup::updateOrCreate(
                [
                    'official_id' => $official->id,
                    'age_group_id' => $attributes['age_group_id'],
                ],
                [
                    'season' => (string) date('Y'),
                    'role' => $attributes['role'] ?? null,
                    'license_levels' => $attributes['license_levels'] ?? null,
                    'registration_status' => $attributes['verification_status'] ?? null,
                    'status_date' => $attributes['reviewed_at'] ?? $attributes['submitted_at'] ?? now(),
                    'notes' => null,
                ]
            );
        }

        return $official;
    }

    private function upsertPlayer(Club $club, string $name, array $attributes): Player
    {
        $player = Player::updateOrCreate(
            ['club_id' => $club->id, 'name' => $name],
            $attributes + ['club_id' => $club->id]
        );

        if (!empty($attributes['primary_age_group_id'])) {
            PlayerAgeGroup::updateOrCreate(
                [
                    'player_id' => $player->id,
                    'age_group_id' => $attributes['primary_age_group_id'],
                ],
                [
                    'season' => (string) date('Y'),
                    'jersey_number' => $attributes['jersey_number'] ?? null,
                    'position' => $attributes['position'] ?? null,
                    'registration_status' => $attributes['verification_status'] ?? null,
                    'status_date' => $attributes['reviewed_at'] ?? $attributes['submitted_at'] ?? now(),
                ]
            );
        }

        return $player;
    }

    private function upsertLineup(Club $club, string $title, array $attributes): LineupList
    {
        return LineupList::updateOrCreate(
            ['club_id' => $club->id, 'title' => $title],
            $attributes + ['club_id' => $club->id]
        );
    }

    private function upsertMatchSchedule(array $identity, array $attributes): MatchSchedule
    {
        return MatchSchedule::updateOrCreate($identity, $attributes + $identity);
    }

    private function upsertInformationResource(array $identity, array $attributes): InformationResource
    {
        return InformationResource::updateOrCreate($identity, $attributes);
    }

    private function seedInformationResourceAsset(string $sourcePath, string $title): array
    {
        if (is_file($sourcePath)) {
            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            $fileName = Str::slug($title).'.'.$extension;
            $targetPath = 'information-resources/'.$fileName;

            Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));

            return [
                'file_path' => $targetPath,
                'file_name' => basename($sourcePath),
                'file_mime' => mime_content_type($sourcePath) ?: 'application/octet-stream',
            ];
        }

        $fallbackName = Str::slug($title).'.pdf';
        $fallbackPath = $this->seedDemoDocument($fallbackName, $title);

        return [
            'file_path' => $fallbackPath,
            'file_name' => $fallbackName,
            'file_mime' => 'application/pdf',
        ];
    }

    private function syncLineupPlayers(LineupList $lineupList, array $entries): void
    {
        $syncData = [];

        foreach ($entries as $index => $entry) {
            if (!$entry['player']) {
                continue;
            }

            $syncData[$entry['player']->id] = [
                'role' => $entry['role'],
                'display_order' => $index + 1,
            ];
        }

        $lineupList->players()->sync($syncData);
    }

    private function seedOfficialBatch(Club $club, array $officials, User $admin): void
    {
        foreach ($officials as $index => $official) {
            $slug = Str::slug($club->short_name.'-'.$official['name']);
            $status = $official['status'];

            $attributes = [
                'role' => $official['role'],
                'age_group_id' => $official['age_group_id'] ?? null,
                'phone' => '08123'.str_pad((string) ($club->id * 100 + $index + 1), 7, '0', STR_PAD_LEFT),
                'email' => $slug.'@official.demo',
                'birth_date' => now()->subYears(28 + $index)->subDays(($index + 1) * 9)->toDateString(),
                'license_number' => strtoupper($club->short_name).'-OFF-'.str_pad((string) ($index + 10), 3, '0', STR_PAD_LEFT),
                'photo_path' => $this->seedDemoImage("{$slug}.svg", $official['name']),
                'license_file_path' => $this->seedDemoDocument("{$slug}-license.pdf", 'Lisensi '.$official['name']),
                'identity_file_path' => $this->seedDemoDocument("{$slug}-ktp.pdf", 'KTP '.$official['name']),
                'is_active' => true,
                'notes' => 'Official demo untuk kebutuhan pengujian workflow.',
                'verification_status' => $status,
                'submitted_at' => in_array($status, [Official::STATUS_SUBMITTED, Official::STATUS_APPROVED, Official::STATUS_REJECTED, Official::STATUS_REVISION], true) ? now()->subDays(3) : null,
                'reviewed_by' => in_array($status, [Official::STATUS_APPROVED, Official::STATUS_REJECTED, Official::STATUS_REVISION], true) ? $admin->id : null,
                'reviewed_at' => in_array($status, [Official::STATUS_APPROVED, Official::STATUS_REJECTED, Official::STATUS_REVISION], true) ? now()->subDay() : null,
            ];

            if ($status === Official::STATUS_APPROVED) {
                $attributes['verification_notes'] = 'Official demo sudah diverifikasi.';
            } elseif ($status === Official::STATUS_REVISION) {
                $attributes['verification_notes'] = 'Official demo perlu unggah ulang dokumen lisensi.';
            } elseif ($status === Official::STATUS_REJECTED) {
                $attributes['verification_notes'] = 'Official demo ditolak karena identitas tidak valid.';
            }

            $this->upsertOfficial($club, $official['name'], $attributes);
        }
    }

    private function seedPlayerBatch(Club $club, AgeGroup $ageGroup, array $players): void
    {
        foreach ($players as $player) {
            $slug = Str::slug($club->short_name.'-'.$player['name']);
            $status = $player['status'];

            $attributes = [
                'primary_age_group_id' => $ageGroup->id,
                'jersey_number' => $player['number'],
                'position' => $player['position'],
                'photo_path' => $this->seedDemoImage("{$slug}.svg", $player['name']),
                'family_card_file_path' => $this->seedDemoDocument("{$slug}-kk.pdf", 'KK '.$player['name']),
                'diploma_file_path' => $this->seedDemoDocument("{$slug}-ijazah.pdf", 'Ijazah '.$player['name']),
                'report_file_path' => $this->seedDemoDocument("{$slug}-rapor.pdf", 'Rapor '.$player['name']),
                'birth_certificate_file_path' => $this->seedDemoDocument("{$slug}-akta.pdf", 'Akta '.$player['name']),
                'birth_date' => now()->subYears($ageGroup->max_age ?? 14)->subDays($player['number'] * 12)->toDateString(),
                'height_cm' => 135 + ($player['number'] % 20),
                'weight_kg' => 32 + ($player['number'] % 15),
                'is_captain' => false,
                'notes' => 'Pemain demo untuk roster DSP.',
                'verification_status' => $status,
                'submitted_at' => in_array($status, [Player::STATUS_SUBMITTED, Player::STATUS_APPROVED, Player::STATUS_REJECTED, Player::STATUS_REVISION], true) ? now()->subDays(3) : null,
                'reviewed_at' => in_array($status, [Player::STATUS_APPROVED, Player::STATUS_REJECTED, Player::STATUS_REVISION], true) ? now()->subDay() : null,
            ];

            if ($status === Player::STATUS_APPROVED) {
                $attributes['verification_notes'] = 'Pemain demo sudah diverifikasi.';
            } elseif ($status === Player::STATUS_REVISION) {
                $attributes['verification_notes'] = 'Pemain demo perlu perbaikan dokumen.';
            } elseif ($status === Player::STATUS_REJECTED) {
                $attributes['verification_notes'] = 'Pemain demo ditolak karena dokumen tidak valid.';
            }

            $this->upsertPlayer($club, $player['name'], $attributes);
        }
    }

    private function seedDemoDocument(string $filename, string $title): string
    {
        $path = str_contains($filename, '/')
            ? $filename
            : 'demo-documents/'.$filename;

        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->makeSimplePdf($title));
        }

        return $path;
    }

    private function seedDemoImage(string $filename, string $title): string
    {
        $path = str_contains($filename, '/')
            ? $filename
            : 'demo-images/'.$filename;

        if (!Storage::disk('public')->exists($path)) {
            $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <rect width="600" height="600" fill="#eef3ff"/>
  <circle cx="300" cy="220" r="110" fill="#1f5ea8"/>
  <rect x="120" y="360" width="360" height="120" rx="24" fill="#dbe7ff"/>
  <text x="300" y="545" font-size="34" text-anchor="middle" fill="#163861" font-family="Arial, sans-serif">{$safeTitle}</text>
</svg>
SVG;
            Storage::disk('public')->put($path, $svg);
        }

        return $path;
    }

    private function makeSimplePdf(string $title): string
    {
        $safeTitle = substr(preg_replace('/[^A-Za-z0-9 .-]/', '', $title) ?: 'DSP Demo', 0, 60);
        $stream = "BT /F1 18 Tf 72 720 Td ({$safeTitle}) Tj ET";
        $length = strlen($stream);

        return "%PDF-1.4\n".
            "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n".
            "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n".
            "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources<< /Font<< /F1 5 0 R >> >> >>endobj\n".
            "4 0 obj<< /Length {$length} >>stream\n{$stream}\nendstream\nendobj\n".
            "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n".
            "xref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000063 00000 n \n0000000122 00000 n \n0000000248 00000 n \n0000000341 00000 n \n".
            "trailer<< /Root 1 0 R /Size 6 >>\nstartxref\n411\n%%EOF";
    }
}
