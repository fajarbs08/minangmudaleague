<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Club as ClubModel;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\Official;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $clubIds = $user->isAdmin() ? Club::query()->select('id') : $user->clubs()->select('id');

        return view('competition.dashboard', [
            'title' => 'Dashboard',
            'stats' => [
                'clubs' => Club::whereIn('id', $clubIds)->count(),
                'officials' => Official::whereIn('club_id', $clubIds)->count(),
                'players' => Player::whereIn('club_id', $clubIds)->count(),
                'lineups' => LineupList::whereIn('club_id', $clubIds)->count(),
                'pending_clubs' => Club::whereIn('id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'pending_officials' => Official::whereIn('club_id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'pending_players' => Player::whereIn('club_id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'pending_lineups' => LineupList::whereIn('club_id', $clubIds)->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
            ],
            'recentPlayers' => Player::with(['club', 'primaryAgeGroup'])->whereIn('club_id', $clubIds)->latest()->take(5)->get(),
            'recentLineups' => LineupList::with(['club', 'ageGroup'])->whereIn('club_id', $clubIds)->latest()->take(5)->get(),
            'clubSummary' => !$user->isAdmin() ? Club::whereIn('id', $clubIds)->latest()->first() : null,
            'adminReviewStats' => $user->isAdmin() ? $this->adminReviewStats() : [],
            'adminQueues' => $user->isAdmin() ? $this->adminQueues() : [],
            'recentSubmissions' => $user->isAdmin() ? $this->recentSubmissions() : collect(),
            'oldestPendingReviews' => $user->isAdmin() ? $this->oldestPendingReviews() : collect(),
            'adminResources' => $user->isAdmin() ? [
                'club_accounts' => User::query()->where('role', 'club')->count(),
                'unused_club_accounts' => User::query()->where('role', 'club')->doesntHave('clubs')->count(),
            ] : [],
        ]);
    }

    public function workflowPdf(Request $request)
    {
        abort_unless($request->user()?->isClubUser(), 403);

        $pdf = Pdf::loadView('pdf.club-workflow', [
            'generatedAt' => now(),
            'steps' => [
                [
                    'number' => '1',
                    'title' => 'Terima Akun dan Login',
                    'description' => 'Tahap awal untuk akun club adalah menerima akses login dan masuk ke dashboard registrasi.',
                    'screenshot' => [
                        'title' => 'Tampilan dashboard akun club',
                        'caption' => 'Penanda pada sidebar menunjukkan urutan menu utama yang dipakai akun club: 1) Klub, 2) Official, 3) Pemain, dan 4) DSP.',
                        'path' => public_path('workflow-screens/dashboard-annotated.png'),
                    ],
                    'details' => [
                        'Gunakan email akun club dan password awal yang diberikan panitia atau admin.',
                        'Login ke sistem registrasi sampai berhasil masuk ke dashboard akun club.',
                        'Pastikan menu utama untuk registrasi seperti Klub, Official, Pemain, dan DSP dapat diakses dengan benar.',
                    ],
                    'result' => 'Akun club berhasil masuk ke dashboard dan siap mulai mengerjakan registrasi.',
                    'accent' => '#ef6b2e',
                    'icon' => 'LOGIN',
                ],
                [
                    'number' => '2',
                    'title' => 'Lengkapi Data Klub',
                    'description' => 'Akun club membuka menu Klub untuk mengisi identitas utama peserta sebelum melanjutkan ke modul lain.',
                    'screenshot' => [
                        'title' => 'Form edit data klub',
                        'caption' => 'Isi identitas klub, unggah dokumen wajib, simpan perubahan, lalu submit verifikasi hanya setelah seluruh data benar.',
                        'path' => public_path('workflow-screens/club-edit-annotated.png'),
                    ],
                    'details' => [
                        'Isi nama klub, nama singkat, nama manajer, zona, kota, tahun berdiri, dan alamat.',
                        'Unggah logo klub, bukti akta SSB, dan surat pernyataan dalam format yang diterima sistem.',
                        'Periksa ulang apakah seluruh identitas klub sudah benar dan sama dengan dokumen pendukung yang diunggah.',
                    ],
                    'result' => 'Profil klub lengkap dan siap diajukan ke verifikasi atau dilanjutkan ke input official dan pemain.',
                    'accent' => '#ff9f43',
                    'icon' => 'KLUB',
                ],
                [
                    'number' => '3',
                    'title' => 'Input Data Official',
                    'description' => 'Akun club mendaftarkan setiap official secara terpisah lengkap dengan identitas, dokumen, dan penugasan.',
                    'screenshot' => [
                        'title' => 'Form input official',
                        'caption' => 'Pilih klub, isi identitas official, unggah lisensi dan dokumen pendukung, lalu simpan data official.',
                        'path' => public_path('workflow-screens/official-create-annotated.png'),
                    ],
                    'details' => [
                        'Isi klub, peran official, nama, nomor lisensi, telepon, email, tempat lahir, tanggal lahir, dan kewarganegaraan.',
                        'Unggah pas foto 3x4, bukti lisensi, serta KTP atau identitas lain yang diminta.',
                        'Tambahkan kelompok usia yang diikuti, jabatan per kelompok usia, level lisensi, dan catatan bila diperlukan.',
                    ],
                    'result' => 'Data official tersimpan sebagai draft dan dapat diedit, diajukan, atau direview kemudian.',
                    'accent' => '#43aa8b',
                    'icon' => 'OFC',
                ],
                [
                    'number' => '4',
                    'title' => 'Input Data Pemain',
                    'description' => 'Akun club mengisi data pemain satu per satu lengkap dengan dokumen administrasi dan kelompok usia.',
                    'screenshot' => [
                        'title' => 'Form input pemain',
                        'caption' => 'Isi identitas pemain, unggah dokumen administrasi, lalu atur kelompok usia, posisi, dan detail registrasi sebelum menyimpan.',
                        'path' => public_path('workflow-screens/player-create-annotated.png'),
                    ],
                    'details' => [
                        'Isi identitas pemain seperti nama, nama ibu kandung, sekolah, nomor registrasi, tinggi, berat, tempat lahir, tanggal lahir, dan dominant foot.',
                        'Unggah pas foto 3x4, file NISN, ijazah, rapor, dan akta kelahiran sesuai kebutuhan verifikasi.',
                        'Tetapkan kelompok usia, musim, nomor punggung, posisi, serta catatan per kelompok usia. Satu pemain dapat tercatat di lebih dari satu kelompok usia.',
                    ],
                    'result' => 'Data pemain masuk ke daftar registrasi dan siap dilanjutkan ke penyusunan roster pertandingan.',
                    'accent' => '#5f6df8',
                    'icon' => 'PLY',
                ],
                [
                    'number' => '5',
                    'title' => 'Susun DSP per Pertandingan',
                    'description' => 'Setelah data pemain tersedia, akun club membuat DSP untuk pertandingan yang akan dijalani.',
                    'screenshot' => [
                        'title' => 'Form penyusunan DSP',
                        'caption' => 'Pilih klub dan kelompok usia, tentukan starter serta cadangan, lalu simpan DSP setelah roster sesuai aturan.',
                        'path' => public_path('workflow-screens/lineup-create-annotated.png'),
                    ],
                    'details' => [
                        'Pilih klub dan kelompok usia, lalu isi judul DSP, match day, tanggal pertandingan, nama pelatih, warna jersey, venue, jam main, dan catatan.',
                        'Pilih pemain yang tersedia ke dalam daftar starter dan cadangan sesuai filter klub dan kelompok usia.',
                        'Isi urutan tampil pemain pada roster DSP. Sistem menampilkan panduan jumlah starter dan batas maksimal cadangan.',
                    ],
                    'result' => 'DSP tersimpan dan dapat diperiksa ulang sebelum diajukan ke verifikasi.',
                    'accent' => '#e65252',
                    'icon' => 'DSP',
                ],
                [
                    'number' => '6',
                    'title' => 'Ajukan Verifikasi',
                    'description' => 'Setelah data dianggap siap, akun club harus mengajukan item terkait ke proses verifikasi.',
                    'screenshot' => [
                        'title' => 'Panel submit verifikasi',
                        'caption' => 'Tombol submit hanya dipakai setelah data lengkap. Area bertanda menunjukkan aksi akhir untuk mengirim item ke proses review.',
                        'path' => public_path('workflow-screens/submit-annotated.png'),
                    ],
                    'details' => [
                        'Pada data klub, official, pemain, dan DSP, club menekan tombol Submit Verifikasi ketika data sudah lengkap.',
                        'Setelah dikirim, status berubah menjadi Dalam Proses atau submitted dan waktu pengajuan tercatat di sistem.',
                        'Data dianggap lengkap bila semua field penting terisi, dokumen wajib sudah diunggah, identitas sesuai, kelompok usia sudah ditetapkan, dan tidak ada informasi yang masih kosong atau bertentangan.',
                        'Untuk klub, lengkap berarti profil klub, manajer, alamat, logo, akta, dan surat pernyataan sudah siap diperiksa.',
                        'Untuk official, lengkap berarti identitas, peran, lisensi, dokumen pendukung, dan kelompok usia sudah sesuai kebutuhan kompetisi.',
                        'Untuk pemain, lengkap berarti identitas pemain, dokumen administrasi, kelompok usia, posisi, dan nomor punggung sudah benar.',
                        'Untuk DSP, lengkap berarti pertandingan, pelatih, kelompok usia, starter, cadangan, dan urutan roster sudah sesuai aturan sistem.',
                        'Sebelum submit, akun club wajib membuka ulang item terkait dan memastikan tidak ada file salah, file kosong, atau data yang belum diperbarui.',
                    ],
                    'result' => 'Item yang diajukan masuk ke antrian review admin.',
                    'accent' => '#1f7a8c',
                    'icon' => 'SUBMIT',
                ],
                [
                    'number' => '7',
                    'title' => 'Tindak Lanjut Hasil Review',
                    'description' => 'Akun club wajib memantau hasil review dan menindaklanjuti setiap status dengan benar.',
                    'details' => [
                        'Jika status Approved atau Diterima, artinya data dinilai sesuai oleh admin. Akun club tidak perlu submit ulang untuk item tersebut, tetapi harus memastikan data yang sudah disetujui dipakai secara konsisten pada proses berikutnya.',
                        'Sesudah approved, club dapat melanjutkan ke tahapan lanjutan seperti melengkapi modul lain, menyiapkan DSP, atau mengunduh keluaran seperti ID Card bila fitur itu tersedia pada modul terkait.',
                        'Jika status Revision atau Perlu Revisi, artinya data masih bisa diperbaiki oleh club. Akun club harus membuka item yang direvisi, membaca catatan admin secara teliti, memperbaiki bagian yang diminta, memeriksa ulang dokumen, lalu menekan Submit Verifikasi kembali.',
                        'Jika status Rejected atau Ditolak, artinya data tidak diterima dalam kondisi saat ini. Akun club harus menganggap item tersebut belum lolos verifikasi dan wajib meninjau penyebab penolakan sebelum melanjutkan.',
                        'Tindakan saat rejected: baca catatan admin, cocokan dengan field dan dokumen yang ada, perbaiki semua data yang tidak valid atau tidak sesuai, lalu pastikan ke admin atau panitia apakah item boleh diedit langsung atau perlu dibuka ulang secara administratif.',
                        'Bila item rejected masih bisa diedit oleh sistem atau oleh arahan admin, club harus memperbaiki seluruh kekurangan, bukan hanya satu bagian yang paling terlihat, lalu ajukan ulang hanya setelah seluruh syarat benar-benar terpenuhi.',
                        'Jika penolakan terjadi karena dokumen tidak jelas, dokumen salah, data identitas tidak cocok, kelompok usia tidak sesuai, roster DSP tidak memenuhi aturan, atau informasi penting kosong, maka semua sumber masalah itu harus dibereskan sebelum mencoba mengajukan ulang.',
                        'Club tidak boleh menganggap approved berarti pekerjaan selesai total. Semua modul yang masih draft, submitted, revision, atau rejected tetap harus dipantau sampai seluruh kebutuhan kompetisi berstatus diterima.',
                        'Dari dashboard dan modul detail, club juga dapat membuka atau mengunduh PDF ini, melihat status verifikasi, dan mengakses keluaran seperti ID Card pemain atau official serta lembar DSP.',
                    ],
                    'result' => 'Workflow dianggap selesai saat seluruh data yang diperlukan berstatus Diterima.',
                    'accent' => '#b5179e',
                    'icon' => 'FINAL',
                ],
            ],
            'completionChecks' => [
                'Semua field utama pada modul yang sedang dikerjakan sudah terisi dan tidak ada data penting yang kosong.',
                'Nama, tanggal lahir, identitas, kelompok usia, jabatan, posisi, dan nomor punggung sudah sesuai dokumen pendukung.',
                'Dokumen wajib sudah diunggah, dapat dibuka, tidak buram, tidak terpotong, dan milik orang atau klub yang benar.',
                'Tidak ada pertentangan data antar field, antar dokumen, atau antar modul yang diinput oleh akun club.',
                'Roster DSP sudah mengikuti aturan jumlah starter, cadangan, urutan pemain, dan filter klub atau kelompok usia.',
                'Semua catatan review sebelumnya sudah diperbaiki seluruhnya sebelum submit ulang.',
            ],
            'statusGuides' => [
                [
                    'label' => 'Approved / Diterima',
                    'color' => '#0f9d58',
                    'body' => 'Item dinyatakan lolos verifikasi. Akun club tidak perlu submit ulang untuk item itu. Yang harus dilakukan adalah melanjutkan pekerjaan ke modul lain yang belum selesai, menjaga konsistensi data yang sudah diterima, dan memakai hasil approved sebagai dasar proses berikutnya.',
                ],
                [
                    'label' => 'Revision / Perlu Revisi',
                    'color' => '#d97706',
                    'body' => 'Item masih bisa diperbaiki. Akun club harus membuka detail data, membaca catatan review, memperbaiki semua bagian yang diminta, mengganti dokumen jika perlu, lalu memeriksa ulang kelengkapan sebelum menekan Submit Verifikasi kembali.',
                ],
                [
                    'label' => 'Rejected / Ditolak',
                    'color' => '#dc2626',
                    'body' => 'Item belum diterima dan tidak boleh dianggap selesai. Akun club harus menelusuri sebab penolakan, memperbaiki seluruh sumber masalah, memastikan apakah item masih dapat diedit langsung atau perlu dibuka ulang, lalu baru mengajukan kembali setelah syarat benar-benar terpenuhi.',
                ],
            ],
            'rejectedActions' => [
                'Baca catatan review sampai jelas field atau dokumen mana yang menjadi penyebab penolakan.',
                'Bandingkan data di sistem dengan dokumen fisik atau dokumen sumber untuk memastikan tidak ada salah identitas atau salah unggah.',
                'Perbaiki seluruh bagian yang tidak valid: data kosong, dokumen salah, dokumen tidak terbaca, kelompok usia tidak cocok, posisi atau jabatan keliru, atau roster DSP tidak sesuai aturan.',
                'Lakukan pengecekan ulang menyeluruh pada item yang ditolak agar masalah yang sama tidak berulang pada submit berikutnya.',
                'Hubungi panitia atau pihak verifikator jika status reject membuat item perlu dibuka ulang atau jika catatan review belum cukup jelas untuk ditindaklanjuti.',
            ],
        ])->setPaper('a4', 'portrait');

        $fileName = 'tahapan-workflow-dashboard-club.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function adminManualPdf(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $pdf = Pdf::loadView('manuals.admin', [
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileName = 'manual-admin-minang-muda-league.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function clubManualPdf(Request $request)
    {
        abort_unless($request->user()?->isClubUser(), 403);

        $pdf = Pdf::loadView('manuals.club', [
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $fileName = 'manual-club-minang-muda-league.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    private function adminReviewStats(): array
    {
        return [
            [
                'label' => 'Perlu Review Admin',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_SUBMITTED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_SUBMITTED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_SUBMITTED)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
                'hint' => 'Semua submission dengan status Dalam Proses.',
                'class' => 'border-warning border-opacity-25',
                'href' => route('dashboard.index').'#queue-admin',
            ],
            [
                'label' => 'Perlu Revisi Club',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_REVISION)->count()
                    + Official::query()->where('verification_status', Official::STATUS_REVISION)->count()
                    + Player::query()->where('verification_status', Player::STATUS_REVISION)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_REVISION)->count(),
                'hint' => 'Item yang sudah dikembalikan ke club.',
                'class' => 'border-info border-opacity-25',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
            [
                'label' => 'Disetujui',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_APPROVED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_APPROVED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_APPROVED)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_APPROVED)->count(),
                'hint' => 'Seluruh data yang sudah lolos verifikasi.',
                'class' => 'border-success border-opacity-25',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
            [
                'label' => 'Ditolak',
                'value' => Club::query()->where('verification_status', ClubModel::STATUS_REJECTED)->count()
                    + Official::query()->where('verification_status', Official::STATUS_REJECTED)->count()
                    + Player::query()->where('verification_status', Player::STATUS_REJECTED)->count()
                    + LineupList::query()->where('verification_status', LineupList::STATUS_REJECTED)->count(),
                'hint' => 'Data yang perlu keputusan lanjutan panitia.',
                'class' => 'border-danger border-opacity-25',
                'href' => route('dashboard.index').'#submission-terbaru',
            ],
        ];
    }

    private function adminQueues(): array
    {
        return [
            [
                'label' => 'Review Klub',
                'count' => Club::query()->where('verification_status', ClubModel::STATUS_SUBMITTED)->count(),
                'href' => route('clubs.index', ['status' => ClubModel::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Review Official',
                'count' => Official::query()->where('verification_status', Official::STATUS_SUBMITTED)->count(),
                'href' => route('officials.index', ['status' => Official::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Review Pemain',
                'count' => Player::query()->where('verification_status', Player::STATUS_SUBMITTED)->count(),
                'href' => route('players.index', ['status' => Player::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Review DSP',
                'count' => LineupList::query()->where('verification_status', LineupList::STATUS_SUBMITTED)->count(),
                'href' => route('lineup-lists.index', ['status' => LineupList::STATUS_SUBMITTED]),
            ],
            [
                'label' => 'Akun Club Belum Dipakai',
                'count' => User::query()->where('role', 'club')->doesntHave('clubs')->count(),
                'href' => route('club-accounts.create'),
            ],
        ];
    }

    private function recentSubmissions(): Collection
    {
        return collect([
            Club::query()
                ->with('reviewer')
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (Club $club) => [
                    'type' => 'Klub',
                    'name' => $club->name,
                    'status' => $club->verification_status,
                    'club' => $club->name,
                    'submitted_at' => $club->submitted_at,
                    'reviewed_by' => $club->reviewer?->name,
                    'href' => route('clubs.index', ['search' => $club->name]),
                ]),
            Official::query()
                ->with(['club', 'reviewer'])
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (Official $official) => [
                    'type' => 'Official',
                    'name' => $official->name,
                    'status' => $official->verification_status,
                    'club' => $official->club?->name,
                    'submitted_at' => $official->submitted_at,
                    'reviewed_by' => $official->reviewer?->name,
                    'href' => route('officials.show', $official),
                ]),
            Player::query()
                ->with(['club', 'reviewer'])
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (Player $player) => [
                    'type' => 'Pemain',
                    'name' => $player->name,
                    'status' => $player->verification_status,
                    'club' => $player->club?->name,
                    'submitted_at' => $player->submitted_at,
                    'reviewed_by' => $player->reviewer?->name,
                    'href' => route('players.show', $player),
                ]),
            LineupList::query()
                ->with(['club', 'reviewer'])
                ->latest('submitted_at')
                ->take(4)
                ->get()
                ->map(fn (LineupList $lineup) => [
                    'type' => 'DSP',
                    'name' => $lineup->title,
                    'status' => $lineup->verification_status,
                    'club' => $lineup->club?->name,
                    'submitted_at' => $lineup->submitted_at,
                    'reviewed_by' => $lineup->reviewer?->name,
                    'href' => route('lineup-lists.show', $lineup),
                ]),
        ])
            ->flatten(1)
            ->filter(fn (array $item) => $item['submitted_at'])
            ->sortByDesc('submitted_at')
            ->take(8)
            ->values();
    }

    private function oldestPendingReviews(): Collection
    {
        return collect([
            Club::query()
                ->where('verification_status', ClubModel::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (Club $club) => [
                    'type' => 'Klub',
                    'name' => $club->name,
                    'club' => $club->name,
                    'submitted_at' => $club->submitted_at,
                    'href' => route('clubs.index', ['search' => $club->name, 'status' => ClubModel::STATUS_SUBMITTED]),
                ]),
            Official::query()
                ->with('club')
                ->where('verification_status', Official::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (Official $official) => [
                    'type' => 'Official',
                    'name' => $official->name,
                    'club' => $official->club?->name,
                    'submitted_at' => $official->submitted_at,
                    'href' => route('officials.show', $official),
                ]),
            Player::query()
                ->with('club')
                ->where('verification_status', Player::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (Player $player) => [
                    'type' => 'Pemain',
                    'name' => $player->name,
                    'club' => $player->club?->name,
                    'submitted_at' => $player->submitted_at,
                    'href' => route('players.show', $player),
                ]),
            LineupList::query()
                ->with('club')
                ->where('verification_status', LineupList::STATUS_SUBMITTED)
                ->oldest('submitted_at')
                ->take(3)
                ->get()
                ->map(fn (LineupList $lineup) => [
                    'type' => 'DSP',
                    'name' => $lineup->title,
                    'club' => $lineup->club?->name,
                    'submitted_at' => $lineup->submitted_at,
                    'href' => route('lineup-lists.show', $lineup),
                ]),
        ])
            ->flatten(1)
            ->filter(fn (array $item) => $item['submitted_at'])
            ->sortBy('submitted_at')
            ->take(6)
            ->values()
            ->map(fn (array $item) => $item + [
                'waiting_label' => $this->formatPendingAge($item['submitted_at']),
            ]);
    }

    private function formatPendingAge($submittedAt): string
    {
        $minutes = (int) $submittedAt->diffInMinutes(now());

        if ($minutes < 60) {
            return $minutes.' menit';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);

        if ($days > 0) {
            return $hours > 0
                ? "{$days} hari {$hours} jam"
                : "{$days} hari";
        }

        return $hours.' jam';
    }
}
