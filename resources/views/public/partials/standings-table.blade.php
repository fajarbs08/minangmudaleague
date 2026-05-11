@php
    $standingsGroups = collect($publicStandings ?? []);

    $clubBadge = function ($row): array {
        $logoUrl = $row['club_logo_url'] ?? null;

        if (filled($logoUrl)) {
            $label = data_get($row, 'club_short_name') ?: data_get($row, 'club_name') ?: 'Klub';

            return [
                'logo_url' => $logoUrl,
                'label' => $label,
                'initials' => '',
            ];
        }

        $club = $row['club'] ?? null;

        if ($club && filled($club->logo_file_url)) {
            $logoUrl = $club->logo_file_url;
        }

        if (! filled($logoUrl) && $club && filled($club->logo_url)) {
            $logoUrl = str_starts_with($club->logo_url, 'http')
                ? $club->logo_url
                : url('/storage/'.ltrim($club->logo_url, '/'));
        }

        $label = data_get($row, 'club_short_name') ?: data_get($row, 'club_name') ?: 'Klub';
        $initials = \Illuminate\Support\Str::of($label)
            ->replaceMatches('/[^A-Za-z0-9 ]+/', ' ')
            ->upper()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
            ->implode('');

        return [
            'logo_url' => $logoUrl,
            'label' => $label,
            'initials' => $initials !== '' ? $initials : 'KL',
        ];
    };

    $formatNumber = function ($value, int $decimals = 0): string {
        return number_format((float) ($value ?? 0), $decimals, '.', ',');
    };

    $standingsFilterOptions = $standingsFilterOptions ?? [
        'age_groups' => collect(),
        'years' => collect(),
        'dates' => collect(),
        'clubs' => collect(),
    ];
    $selectedPublicSeason = $selectedPublicSeason ?? null;
    $publicSeasonOptions = $publicSeasonOptions ?? collect();
    $publicSeasonQuery = $publicSeasonQuery ?? [];

    $standingsFilters = $standingsFilters ?? [
        'age_group_id' => null,
        'year' => null,
        'date' => null,
        'club_id' => null,
    ];

    $topScorers = $topScorers ?? collect();
    $topAssists = $topAssists ?? collect();

@endphp

<div data-standings-table-wrap>
    <div class="latest-world-ranking-table">
        <div class="ranking-category-items">
            <form method="GET" action="{{ route('public.standings') }}" data-standings-filter-form>
                <div class="row g-4 align-items-center">
                    <div class="col-12">
                        <div class="ranking-category-grid">
                            <div class="ranking-category-field">
                                <div class="form-clt">
                                    <div class="form">
                                        <select class="single-select w-100" name="season" onchange="window.loadStandingsFilter(this)">
                                            @foreach ($publicSeasonOptions as $season)
                                                <option value="{{ $season->slug }}" @selected(($selectedPublicSeason?->id ?? 0) === $season->id)>{{ $season->name }}{{ $season->is_active ? ' • aktif' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="ranking-category-field">
                                <div class="form-clt">
                                    <div class="form">
                                        <select class="single-select w-100" name="age_group_id" onchange="window.loadStandingsFilter(this)">
                                            <option value="">Semua kelompok usia</option>
                                            @foreach ($standingsFilterOptions['age_groups'] as $option)
                                                <option value="{{ $option['value'] }}" @selected((string) data_get($standingsFilters, 'age_group_id', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="ranking-category-field">
                                <div class="form-clt">
                                    <div class="form">
                                        <select class="single-select w-100" name="year" onchange="window.loadStandingsFilter(this)">
                                            <option value="">Semua tahun</option>
                                            @foreach ($standingsFilterOptions['years'] as $option)
                                                <option value="{{ $option['value'] }}" @selected((string) data_get($standingsFilters, 'year', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="ranking-category-field">
                                <div class="form-clt">
                                    <div class="form">
                                        <select class="single-select w-100" name="date" onchange="window.loadStandingsFilter(this)">
                                            <option value="">Semua tanggal</option>
                                            @foreach ($standingsFilterOptions['dates'] as $option)
                                                <option value="{{ $option['value'] }}" @selected((string) data_get($standingsFilters, 'date', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="ranking-category-field">
                                <div class="form-clt">
                                    <div class="form">
                                        <select class="single-select w-100" name="club_id" onchange="window.loadStandingsFilter(this)">
                                            <option value="">Semua klub</option>
                                            @foreach ($standingsFilterOptions['clubs'] as $option)
                                                <option value="{{ $option['value'] }}" @selected((string) data_get($standingsFilters, 'club_id', '') === (string) $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="standings-section-grid">
            @forelse ($standingsGroups as $group)
                @php
                    $groupId = $group['age_group']?->id ?? $loop->index;
                    $groupRows = collect($group['rows'] ?? []);
                    $groupLastUpdatedAt = $group['last_match_date'] ?? null;
                @endphp
                <section class="standings-section-card" id="standings-age-{{ $groupId }}">
                    <div class="standings-section-head">
                        <div>
                            <h4>{{ $group['age_group']?->name ?: 'Klasemen' }}</h4>
                            <p>
                                @if ($groupLastUpdatedAt)
                                    Update terakhir {{ \Illuminate\Support\Carbon::instance($groupLastUpdatedAt)->translatedFormat('d F Y') }}
                                @else
                                    Belum ada pertandingan selesai pada kelompok usia ini.
                                @endif
                            </p>
                        </div>
                        <span class="standings-section-badge">{{ $groupRows->count() }} klub</span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Peringkat</th><th>Nama Klub</th><th>Poin Total</th><th>Poin Sebelumnya</th><th>+/- Poin</th><th>Riwayat Laga</th><th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groupRows as $row)
                                    @php $clubIdentity = $clubBadge($row); @endphp
                                    <tr>
                                        <td>{{ str_pad((string) ($row['position'] ?? 0), 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="team">
                                                @if (filled($clubIdentity['logo_url'] ?? null))
                                                    <img src="{{ $clubIdentity['logo_url'] }}" alt="{{ $clubIdentity['label'] }}" loading="lazy" decoding="async" width="36" height="36">
                                                @else
                                                    <span class="lap-team-badge" aria-hidden="true">{{ $clubIdentity['initials'] }}</span>
                                                @endif
                                                {{ $row['club_short_name'] ?? $row['club_name'] ?? 'TBD' }}
                                            </div>
                                        </td>
                                        <td>{{ $formatNumber(data_get($row, 'points', 0), 2) }}</td>
                                        <td>{{ $formatNumber(data_get($row, 'previous_points', 0), 2) }}</td>
                                        <td class="{{ data_get($row, 'points_delta', 0) >= 0 ? 'positive' : 'negative' }}">{{ data_get($row, 'points_delta', 0) >= 0 ? '+' : '' }}{{ $formatNumber(data_get($row, 'points_delta', 0), 2) }}</td>
                                        <td>
                                            @php $recentForm = array_slice($row['recent_form'] ?? [], 0, 2); @endphp
                                            @forelse ($recentForm as $form)
                                                <span class="badge {{ strtolower($form) }}">{{ strtoupper($form) }}</span>
                                            @empty
                                                <span class="badge d">D</span><span class="badge d">D</span>
                                            @endforelse
                                        </td>
                                        <td class="text-center">@include('public.partials.table-detail-link', ['href' => filled($row['club_public_slug'] ?? null) ? route('public.clubs.show', ['clubSlug' => $row['club_public_slug']] + $publicSeasonQuery) : route('public.clubs', $publicSeasonQuery)])</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data klasemen.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <section class="standings-section-card">
                    <div class="table-responsive">
                        <table>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data klasemen.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforelse
        </div>

        <div class="standings-leaderboard-grid">
            @foreach ([
                [
                    'title' => 'Top Skor',
                    'description' => 'Pencetak gol terbanyak dari laga liga yang sudah selesai.',
                    'metric' => 'Gol',
                    'groups' => $topScorers,
                    'empty' => 'Belum ada data top goal untuk filter ini.',
                ],
                [
                    'title' => 'Top Assist',
                    'description' => 'Pemberi assist terbanyak dari laga liga yang sudah selesai.',
                    'metric' => 'Assist',
                    'groups' => $topAssists,
                    'empty' => 'Belum ada data top assist untuk filter ini.',
                ],
            ] as $leaderboard)
                <div class="standings-leaderboard-card">
                    <div class="leaderboard-head">
                        <div>
                            <h4>{{ $leaderboard['title'] }}</h4>
                            <p>{{ $leaderboard['description'] }}</p>
                        </div>
                        <span class="standings-leaderboard-badge">{{ collect($leaderboard['groups'])->sum(fn ($group) => collect($group['rows'] ?? [])->count()) }}</span>
                    </div>
                    <div class="standings-leaderboard-body">
                        @forelse ($leaderboard['groups'] as $group)
                            <div class="standings-leaderboard-group">
                                <h5>{{ $group['age_group']?->name ?: '-' }}</h5>
                                <div class="standings-leaderboard-list">
                                    @foreach ($group['rows'] as $row)
                                        <div class="standings-leaderboard-item">
                                            <div class="standings-leaderboard-rank">{{ str_pad((string) $row['position'], 2, '0', STR_PAD_LEFT) }}</div>
                                            <div class="standings-leaderboard-player">
                                                <strong>{{ $row['player_name'] }}</strong>
                                                <span>{{ $row['club_name'] }}</span>
                                            </div>
                                            <div class="standings-leaderboard-metric" aria-label="{{ $leaderboard['metric'] }}">{{ $row['total'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="standings-leaderboard-empty">{{ $leaderboard['empty'] }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
