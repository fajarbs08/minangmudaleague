<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Club as ClubModel;
use App\Models\Club;
use App\Models\InformationResource;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\Player;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function publicHome()
    {
        return view('public.home', $this->publicPageData([
            'title' => 'Liga Anak Piaman Laweh',
            'seoTitle' => 'Liga Anak Piaman Laweh | Portal Kompetisi Sepak Bola Anak',
            'activePublicPage' => 'home',
            'seoDescription' => 'Platform resmi Liga Anak Piaman Laweh untuk jadwal pertandingan, hasil, klasemen, daftar klub, sponsor, dan pusat informasi kompetisi.',
        ]));
    }

    public function publicSchedule()
    {
        return view('public.schedule', $this->publicPageData([
            'title' => 'Jadwal Pertandingan',
            'seoTitle' => 'Jadwal Liga Anak Piaman Laweh | Pertandingan Terbaru',
            'activePublicPage' => 'schedule',
            'bannerTitle' => 'Jadwal Pertandingan',
            'bannerCurrent' => 'Jadwal',
            'seoDescription' => 'Lihat jadwal pertandingan terbaru Liga Anak Piaman Laweh lengkap dengan tanggal, jam kick-off, dan klub yang bertanding.',
        ]));
    }

    public function publicResults()
    {
        return view('public.results', $this->publicPageData([
            'title' => 'Hasil Pertandingan',
            'seoTitle' => 'Hasil Liga Anak Piaman Laweh | Skor Pertandingan',
            'activePublicPage' => 'results',
            'bannerTitle' => 'Hasil Pertandingan',
            'bannerCurrent' => 'Hasil',
            'seoDescription' => 'Pantau hasil pertandingan terbaru Liga Anak Piaman Laweh beserta skor akhir dan ringkasan laga.',
        ]));
    }

    public function publicStandingsPage()
    {
        return view('public.standings', $this->publicPageData([
            'title' => 'Klasemen Liga',
            'seoTitle' => 'Klasemen Liga Anak Piaman Laweh | Posisi Klub',
            'activePublicPage' => 'standings',
            'bannerTitle' => 'Klasemen Kompetisi',
            'bannerCurrent' => 'Klasemen',
            'seoDescription' => 'Klasemen sementara Liga Anak Piaman Laweh berdasarkan hasil pertandingan resmi di setiap kelompok usia.',
        ]));
    }

    public function publicClubs()
    {
        return view('public.clubs', $this->publicPageData([
            'title' => 'Klub Peserta',
            'seoTitle' => 'Klub Peserta Liga Anak Piaman Laweh | Profil Tim',
            'activePublicPage' => 'clubs',
            'bannerTitle' => 'Daftar Klub',
            'bannerCurrent' => 'Klub',
            'seoDescription' => 'Daftar klub peserta Liga Anak Piaman Laweh lengkap dengan profil singkat, pemain, dan official terdaftar.',
        ]));
    }

    public function publicSponsors()
    {
        return view('public.sponsors', $this->publicPageData([
            'title' => 'Sponsor Kompetisi',
            'seoTitle' => 'Sponsor Liga Anak Piaman Laweh | Mitra Resmi',
            'activePublicPage' => 'sponsors',
            'bannerTitle' => 'Sponsor Kompetisi',
            'bannerCurrent' => 'Sponsor',
            'featuredSponsors' => $this->publicSponsorsData(),
            'seoDescription' => 'Kenali sponsor dan mitra resmi yang mendukung penyelenggaraan Liga Anak Piaman Laweh.',
        ]));
    }

    public function publicClubShow(string $clubSlug)
    {
        preg_match('/(\d+)$/', $clubSlug, $matches);
        $clubId = isset($matches[1]) ? (int) $matches[1] : 0;

        $club = Club::query()->findOrFail($clubId);

        abort_unless($club->verification_status === Club::STATUS_APPROVED, 404);

        $club->load([
            'players' => fn ($query) => $query
                ->with('primaryAgeGroup')
                ->where('verification_status', Player::STATUS_APPROVED)
                ->orderByDesc('is_captain')
                ->orderBy('name'),
            'officials' => fn ($query) => $query
                ->with('ageGroup')
                ->where('verification_status', Official::STATUS_APPROVED)
                ->where('is_active', true)
                ->orderBy('role')
                ->orderBy('name'),
        ]);

        $clubMatches = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->where(function ($query) use ($club) {
                $query->where('club_a_id', $club->id)
                    ->orWhere('club_b_id', $club->id);
            })
            ->orderByDesc('match_date')
            ->orderByDesc('kickoff_time')
            ->limit(6)
            ->get();

        return view('public.club-show', $this->publicPageData([
            'title' => $club->name.' - Klub Peserta',
            'activePublicPage' => 'clubs',
            'bannerTitle' => $club->name,
            'bannerCurrent' => $club->short_name ?: $club->name,
            'club' => $club,
            'clubPlayers' => $club->players,
            'clubOfficials' => $club->officials,
            'clubRecentMatches' => $clubMatches,
            'seoTitle' => $club->name.' | Liga Anak Piaman Laweh',
            'seoDescription' => 'Profil klub '.$club->name.' di Liga Anak Piaman Laweh, termasuk pemain, official, dan riwayat pertandingan terbaru.',
            'seoImage' => $this->defaultSeoImageUrl(),
        ]));
    }

    public function publicInformation()
    {
        $category = request()->string('category')->value();
        $search = request()->string('search')->value();

        $informationQuery = InformationResource::query()
            ->with('creator')
            ->where('is_published', true)
            ->where('visibility', InformationResource::VISIBILITY_PUBLIC);

        $publishedResources = (clone $informationQuery)
            ->when($category, fn ($query, $value) => $query->where('category', $value))
            ->when($search, fn ($query, $value) => $query->where(function ($subQuery) use ($value) {
                $subQuery->where('title', 'like', "%{$value}%")
                    ->orWhere('description', 'like', "%{$value}%");
            }))
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $resourceCategories = (clone $informationQuery)
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $latestResources = (clone $informationQuery)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('public.information', $this->publicPageData([
            'title' => 'Informasi Kompetisi',
            'seoTitle' => 'Informasi Liga Anak Piaman Laweh | Panduan & Dokumen',
            'activePublicPage' => 'information',
            'bannerTitle' => 'Informasi Kompetisi',
            'bannerCurrent' => 'Informasi',
            'publishedResources' => $publishedResources,
            'resourceCategories' => $resourceCategories,
            'latestResources' => $latestResources,
            'activeInformationCategory' => $category,
            'informationSearch' => $search,
            'seoDescription' => 'Pusat informasi publik Liga Anak Piaman Laweh berisi panduan, template, alur, dan dokumen resmi kompetisi.',
        ]));
    }

    public function publicInformationShow(string $resourceSlug)
    {
        preg_match('/(\d+)$/', $resourceSlug, $matches);
        $resourceId = isset($matches[1]) ? (int) $matches[1] : 0;

        $resource = InformationResource::query()
            ->with('creator')
            ->where('id', $resourceId)
            ->where('is_published', true)
            ->where('visibility', InformationResource::VISIBILITY_PUBLIC)
            ->firstOrFail();

        $relatedResources = InformationResource::query()
            ->with('creator')
            ->where('is_published', true)
            ->where('visibility', InformationResource::VISIBILITY_PUBLIC)
            ->where('id', '!=', $resource->id)
            ->where(function ($query) use ($resource) {
                $query->where('category', $resource->category)
                    ->orWhere('is_pinned', true);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $adjacentResources = InformationResource::query()
            ->with('creator')
            ->where('is_published', true)
            ->where('visibility', InformationResource::VISIBILITY_PUBLIC)
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->values();

        $resourceIndex = $adjacentResources->search(fn (InformationResource $item) => $item->id === $resource->id);
        $previousResource = $resourceIndex !== false && $resourceIndex > 0
            ? $adjacentResources->get($resourceIndex - 1)
            : null;
        $nextResource = $resourceIndex !== false && $resourceIndex < ($adjacentResources->count() - 1)
            ? $adjacentResources->get($resourceIndex + 1)
            : null;

        return view('public.information-show', $this->publicPageData([
            'title' => $resource->title,
            'activePublicPage' => 'information',
            'bannerTitle' => 'Informasi Kompetisi',
            'bannerCurrent' => $resource->title,
            'resource' => $resource,
            'relatedResources' => $relatedResources,
            'previousResource' => $previousResource,
            'nextResource' => $nextResource,
            'resourcePageUrl' => $this->normalizeAbsoluteUrl(route('public.information.show', ['resourceSlug' => $resource->public_slug])),
            'seoTitle' => $resource->title.' | Liga Anak Piaman Laweh',
            'seoDescription' => Str::limit($resource->description ?: 'Dokumen resmi yang dipublikasikan melalui pusat informasi Liga Anak Piaman Laweh.', 155),
            'seoImage' => $this->defaultSeoImageUrl(),
            'seoType' => 'article',
            'seoUrl' => $this->normalizeAbsoluteUrl(route('public.information.show', ['resourceSlug' => $resource->public_slug])),
        ]));
    }

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

    private function publicStandings(): Collection
    {
        return MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->where('competition_format', MatchSchedule::FORMAT_LEAGUE)
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->orderBy('age_group_id')
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->get()
            ->groupBy('age_group_id')
            ->map(function (Collection $matches) {
                $table = collect();

                foreach ($matches as $match) {
                    foreach ([
                        [
                            'club' => $match->clubA,
                            'goals_for' => (int) $match->score_club_a,
                            'goals_against' => (int) $match->score_club_b,
                        ],
                        [
                            'club' => $match->clubB,
                            'goals_for' => (int) $match->score_club_b,
                            'goals_against' => (int) $match->score_club_a,
                        ],
                    ] as $entry) {
                        if (!$entry['club']) {
                            continue;
                        }

                        $clubId = $entry['club']->id;
                        $row = $table->get($clubId, [
                            'club_id' => $clubId,
                            'club_name' => $entry['club']->name,
                            'club_short_name' => $entry['club']->short_name ?: $entry['club']->name,
                            'played' => 0,
                            'won' => 0,
                            'drawn' => 0,
                            'lost' => 0,
                            'goals_for' => 0,
                            'goals_against' => 0,
                            'goal_difference' => 0,
                            'points' => 0,
                        ]);

                        $row['played']++;
                        $row['goals_for'] += $entry['goals_for'];
                        $row['goals_against'] += $entry['goals_against'];

                        if ($entry['goals_for'] > $entry['goals_against']) {
                            $row['won']++;
                            $row['points'] += 3;
                        } elseif ($entry['goals_for'] === $entry['goals_against']) {
                            $row['drawn']++;
                            $row['points'] += 1;
                        } else {
                            $row['lost']++;
                        }

                        $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];

                        $table->put($clubId, $row);
                    }
                }

                return [
                    'age_group' => $matches->first()?->ageGroup,
                    'rows' => $table
                        ->sortBy([
                            ['points', 'desc'],
                            ['goal_difference', 'desc'],
                            ['goals_for', 'desc'],
                            ['club_name', 'asc'],
                        ])
                        ->values()
                        ->map(function (array $row, int $index) {
                            $row['position'] = $index + 1;

                            return $row;
                        })
                        ->take(5)
                        ->values(),
                ];
            })
            ->values();
    }

    private function publicKnockoutBrackets(): Collection
    {
        return MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'goalEvents.scorer', 'goalEvents.assistPlayer'])
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->orderBy('age_group_id')
            ->orderBy('round_order')
            ->orderBy('bracket_slot')
            ->orderBy('match_date')
            ->get()
            ->groupBy('age_group_id')
            ->map(function (Collection $matches) {
                return [
                    'age_group' => $matches->first()?->ageGroup,
                    'rounds' => $matches
                        ->groupBy(fn (MatchSchedule $match) => $match->round_order ?: 1)
                        ->map(function (Collection $roundMatches) {
                            return [
                                'label' => $roundMatches->first()?->round_display_label ?: 'Babak Knockout',
                                'matches' => $roundMatches
                                    ->sortBy(fn (MatchSchedule $match) => $match->bracket_slot ?: PHP_INT_MAX)
                                    ->values(),
                            ];
                        })
                        ->sortKeys()
                        ->values(),
                ];
            })
            ->values();
    }

    private function publicPageData(array $overrides = []): array
    {
        $upcomingMatches = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB'])
            ->whereDate('match_date', '>=', now()->toDateString())
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->limit(12)
            ->get();

        $recentResults = MatchSchedule::query()
            ->with(['ageGroup', 'clubA', 'clubB', 'goalEvents.scorer', 'goalEvents.assistPlayer'])
            ->where('is_finished', true)
            ->whereNotNull('score_club_a')
            ->whereNotNull('score_club_b')
            ->orderByDesc('match_date')
            ->orderByDesc('kickoff_time')
            ->limit(12)
            ->get();

        $featuredClubs = Club::query()
            ->where('verification_status', Club::STATUS_APPROVED)
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $featuredPlayers = Player::query()
            ->with(['club', 'primaryAgeGroup'])
            ->where('verification_status', Player::STATUS_APPROVED)
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $publishedResources = InformationResource::query()
            ->with('creator')
            ->where('is_published', true)
            ->where('visibility', InformationResource::VISIBILITY_PUBLIC)
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $defaults = [
            'title' => 'Liga Anak Piaman Laweh',
            'activePublicPage' => 'home',
            'bannerTitle' => null,
            'bannerCurrent' => null,
            'publicStats' => [
                'clubs' => Club::query()->count(),
                'officials' => Official::query()->count(),
                'players' => Player::query()->count(),
                'lineups' => LineupList::query()->count(),
            ],
            'featuredClubs' => $featuredClubs,
            'featuredPlayers' => $featuredPlayers,
            'upcomingMatches' => $upcomingMatches,
            'recentResults' => $recentResults,
            'headlineMatch' => $upcomingMatches->first(),
            'featuredResult' => $recentResults->first(),
            'publicStandings' => $this->publicStandings(),
            'publicKnockoutBrackets' => $this->publicKnockoutBrackets(),
            'publishedResources' => $publishedResources,
            'featuredSponsors' => $this->publicSponsorsData(),
        ];

        $data = array_merge($defaults, $overrides);
        $defaultSeoTitle = ($data['title'] ?? 'Liga Anak Piaman Laweh') === 'Liga Anak Piaman Laweh'
            ? 'Liga Anak Piaman Laweh | Portal Kompetisi Sepak Bola Anak'
            : ($data['title'] ?? 'Liga Anak Piaman Laweh').' | Liga Anak Piaman Laweh';

        $data['seoTitle'] = $data['seoTitle'] ?? $defaultSeoTitle;
        $data['seoDescription'] = $data['seoDescription'] ?? 'Platform resmi Liga Anak Piaman Laweh untuk informasi kompetisi, jadwal, hasil pertandingan, klasemen, dan data klub peserta.';
        $data['seoImage'] = $data['seoImage'] ?? $this->defaultSeoImageUrl();
        $data['seoUrl'] = $data['seoUrl'] ?? $this->normalizeAbsoluteUrl(url()->current());
        $data['seoType'] = $data['seoType'] ?? 'website';
        $data['seoRobots'] = $data['seoRobots'] ?? 'index,follow';

        return $data;
    }

    private function normalizeAbsoluteUrl(string $url): string
    {
        if (!$this->shouldForceHttpsUrls()) {
            return $url;
        }

        return preg_replace('/^http:/i', 'https:', $url) ?: $url;
    }

    private function defaultSeoImageUrl(): string
    {
        return $this->normalizeAbsoluteUrl(asset('og-share-card.jpg'));
    }

    private function shouldForceHttpsUrls(): bool
    {
        if (Str::startsWith((string) config('app.url'), 'https://')) {
            return true;
        }

        if (app()->runningInConsole() || !app()->bound('request')) {
            return false;
        }

        $request = request();

        return $request->isSecure() || $request->headers->get('x-forwarded-proto') === 'https';
    }

    private function publicSponsorsData(): Collection
    {
        return Sponsor::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Sponsor $sponsor) => [
                'name' => $sponsor->name,
                'short_name' => $sponsor->short_name,
                'logo_url' => $sponsor->logo_url,
                'website_url' => $sponsor->website_url,
                'tier' => $sponsor->tier,
            ]);
    }

    public function clubResources(Request $request)
    {
        abort_unless($request->user()?->isClubUser(), 403);
        $category = $request->string('category')->value();

        return view('competition.club-resources', [
            'title' => 'Pusat Informasi Club',
            'managedResources' => InformationResource::query()
                ->where('is_published', true)
                ->whereIn('visibility', [
                    InformationResource::VISIBILITY_PUBLIC,
                    InformationResource::VISIBILITY_CLUB,
                ])
                ->when($category, fn ($query, $value) => $query->where('category', $value))
                ->orderByDesc('is_pinned')
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get(),
            'activeCategory' => $category,
            'downloadResources' => [
                [
                    'label' => 'Template Surat Pernyataan',
                    'description' => 'File template surat pernyataan yang diisi club lalu diunggah kembali pada data klub.',
                    'open_url' => route('clubs.statement-template'),
                    'download_url' => route('clubs.statement-template'),
                    'badge' => 'Template',
                    'badge_class' => 'bg-primary-subtle text-primary',
                ],
                [
                    'label' => 'Flow Alur Registrasi',
                    'description' => 'Panduan urutan kerja akun club mulai dari data klub, pemain, official, sampai DSP.',
                    'open_url' => route('dashboard.workflow-pdf'),
                    'download_url' => route('dashboard.workflow-pdf', ['download' => 1]),
                    'badge' => 'PDF',
                    'badge_class' => 'bg-success-subtle text-success',
                ],
                [
                    'label' => 'Manual Club',
                    'description' => 'Dokumen panduan penggunaan sistem untuk akun club dalam format PDF.',
                    'open_url' => route('dashboard.club-manual-pdf'),
                    'download_url' => route('dashboard.club-manual-pdf', ['download' => 1]),
                    'badge' => 'PDF',
                    'badge_class' => 'bg-info-subtle text-info',
                ],
            ],
            'upcomingResources' => [
                [
                    'label' => 'Rules Kompetisi',
                    'description' => 'Ketentuan dan regulasi resmi kompetisi.',
                ],
                [
                    'label' => 'Dokumen Tambahan',
                    'description' => 'Dokumen briefing teknis, jadwal teknikal meeting, dan lampiran pendukung lainnya.',
                ],
            ],
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
                        'Unduh template surat pernyataan, isi data klub, tanda tangan, lalu unggah kembali bersama logo klub.',
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
                        'Unggah pas foto 3x4, file KK, ijazah, rapor, dan akta kelahiran sesuai kebutuhan verifikasi.',
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
                        'Untuk klub, lengkap berarti profil klub, manajer, alamat, logo, dan surat pernyataan sudah siap diperiksa.',
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
                        'Club juga dapat membuka atau mengunduh PDF ini sebagai referensi selama proses verifikasi dan administrasi pertandingan.',
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

        $fileName = 'manual-admin-liga-anak-piaman-laweh.pdf';

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

        $fileName = 'manual-club-liga-anak-piaman-laweh.pdf';

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
