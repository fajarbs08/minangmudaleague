@php
    $selectedMatchId = (int) old('match_id', $lineupList->match_id);
    $selectedClubId = old('club_id', $lineupList->club_id ?: $clubs->first()?->id);
    $selectedAgeGroupId = old('age_group_id', $lineupList->age_group_id);
    $starterIds = collect(old('starter_player_ids', $selectedStarters ?? []))->map(fn ($id) => (int) $id)->all();
    $substituteIds = collect(old('substitute_player_ids', $selectedSubstitutes ?? []))->map(fn ($id) => (int) $id)->all();
    $starterOrders = old('starter_orders', $selectedStarterOrders ?? []);
    $substituteOrders = old('substitute_orders', $selectedSubstituteOrders ?? []);
    $selectedJerseys = array_filter(array_merge(
        $selectedStarterJerseys ?? [],
        $selectedSubstituteJerseys ?? [],
        old('starter_jerseys', []),
        old('substitute_jerseys', [])
    ), fn ($value) => $value !== null && $value !== '');
    $orderedStarterIds = collect($starterIds)
        ->sortBy(fn ($id) => [(int) ($starterOrders[$id] ?? 999), $id])
        ->values()
        ->all();
    $orderedSubstituteIds = collect($substituteIds)
        ->sortBy(fn ($id) => [(int) ($substituteOrders[$id] ?? 999), $id])
        ->values()
        ->all();
    $blockedSelectedPlayers = collect($blockedSelectedPlayers ?? []);
    $blockedLineupPlayers = collect($blockedLineupPlayers ?? []);
@endphp

<style>
    [data-lineup-builder],
    [data-lineup-builder] * {
        min-width: 0;
    }

    .lineup-role-toggle {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        max-width: 100%;
        width: 100%;
    }

    .lineup-role-toggle .btn {
        border-radius: 0 !important;
        margin-left: 0 !important;
        white-space: normal;
    }

    .lineup-role-toggle .btn:first-child {
        border-radius: 6px 0 0 6px !important;
    }

    .lineup-role-toggle .btn:last-child {
        border-radius: 0 6px 6px 0 !important;
    }

    .lineup-section-header {
        min-width: 0;
    }

    .lineup-player-name,
    .lineup-player-meta,
    .lineup-selected-player-name,
    .lineup-selected-player-meta {
        overflow-wrap: anywhere;
    }

    .lineup-selected-player {
        min-width: 0;
    }

    .lineup-selected-player-actions {
        flex-shrink: 0;
    }

    @media (max-width: 575.98px) {
        .page-content {
            padding: 0 !important;
        }

        .lineup-form-page-header {
            padding: 1rem 1rem 0.75rem;
            margin-bottom: 0 !important;
            gap: 0.75rem !important;
        }

        .lineup-form-page-header .btn {
            width: 100%;
        }

        .lineup-form-card,
        .lineup-roster-card {
            margin-bottom: 0;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .lineup-form-card {
            margin-top: 0;
        }

        .lineup-form-card > .card-body,
        .lineup-roster-card > .card-body {
            padding: 0.9rem 1rem 1rem;
        }

        .lineup-roster-card {
            margin-top: 0 !important;
        }

        .lineup-section-header {
            align-items: flex-start !important;
            flex-direction: column;
        }

        [data-lineup-form] {
            row-gap: 0;
        }

        [data-lineup-form] .mb-3 {
            margin-bottom: 0.85rem !important;
        }

        [data-lineup-builder] {
            --bs-gutter-x: 0.85rem;
            --bs-gutter-y: 0.85rem;
        }

        [data-lineup-builder] > [class*="col-"] > .border {
            border-radius: 6px !important;
            padding: 0.85rem !important;
        }

        [data-player-option] {
            border-radius: 6px !important;
            padding: 0.85rem !important;
        }

        .lineup-role-toggle {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .lineup-role-toggle .btn {
            width: 100%;
            min-height: 42px;
            padding: 0.45rem 0.35rem;
            font-size: 0.72rem;
            line-height: 1.15;
        }

        .lineup-selected-player {
            align-items: stretch !important;
            flex-direction: column;
        }

        .lineup-selected-player-actions {
            width: 100%;
            justify-content: space-between;
        }

        .lineup-selected-player-actions .input-group {
            width: auto !important;
            flex: 1 1 auto;
        }

        .lineup-form-page-actions {
            margin-top: 1rem !important;
            padding: 0 1rem 1rem;
        }

        .lineup-form-page-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>
<div class="row" data-lineup-form>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Pertandingan <span class="text-danger">*</span></label>
        <select name="match_id" class="form-select" required data-lineup-match>
            <option value="">Pilih pertandingan</option>
            @foreach ($availableMatches as $match)
                @php
                    $usedClubIds = $match->lineupLists->pluck('club_id')->map(fn ($id) => (int) $id)->values();
                    $clubAReady = $usedClubIds->contains((int) $match->club_a_id);
                    $clubBReady = $usedClubIds->contains((int) $match->club_b_id);
                    $optionSuffix = collect([
                        $clubAReady ? ($match->clubA?->short_name ?: $match->clubA?->name).' sudah DSP' : null,
                        $clubBReady ? ($match->clubB?->short_name ?: $match->clubB?->name).' sudah DSP' : null,
                    ])->filter()->implode(' · ');
                @endphp
                <option
                    value="{{ $match->id }}"
                    data-club-a-id="{{ $match->club_a_id }}"
                    data-club-a-name="{{ $match->clubA?->name }}"
                    data-club-b-id="{{ $match->club_b_id }}"
                    data-club-b-name="{{ $match->clubB?->name }}"
                    data-used-club-ids="{{ $usedClubIds->implode(',') }}"
                    data-age-id="{{ $match->age_group_id }}"
                    data-age-name="{{ $match->ageGroup?->name }}"
                    data-match-day="{{ $match->match_day }}"
                    data-venue="{{ $match->venue }}"
                    data-date="{{ optional($match->match_date)->format('d M Y') }}"
                    data-date-value="{{ optional($match->match_date)->format('Y-m-d') }}"
                    data-kickoff="{{ optional($match->kickoff_time)->format('H:i') }}"
                @selected($selectedMatchId === $match->id)
                >
                    {{ $match->clubA?->name }} vs {{ $match->clubB?->name }} · {{ $match->ageGroup?->name }} · {{ $match->match_day }}{{ $optionSuffix ? ' · '.$optionSuffix : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Klub Penyusun DSP <span class="text-danger">*</span></label>
        @if (auth()->user()->isAdmin())
            <select name="club_id" class="form-select" required data-lineup-club data-current-club-id="{{ old('club_id', $lineupList->club_id) }}">
                <option value="">Pilih klub</option>
                @foreach ($clubs as $club)
                    <option value="{{ $club->id }}" @selected((string) $selectedClubId === (string) $club->id)>{{ $club->name }}</option>
                @endforeach
            </select>
            <input type="text" class="form-control d-none" value="" readonly data-lineup-club-readonly>
        @else
            <input type="text" class="form-control" value="{{ $clubs->firstWhere('id', $selectedClubId)?->name ?? '-' }}" disabled data-lineup-club-display>
            <input type="hidden" name="club_id" value="{{ $selectedClubId }}" data-lineup-club>
        @endif
        <input type="hidden" value="{{ $selectedAgeGroupId }}" data-lineup-age>
        <div class="text-muted small mt-2" data-lineup-club-help>Pilih pertandingan terlebih dahulu, lalu sistem akan membatasi klub yang bisa menyusun DSP.</div>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kelompok Usia</label>
        <input type="text" class="form-control" value="{{ $ageGroups->firstWhere('id', $selectedAgeGroupId)?->name ?? '-' }}" readonly data-lineup-age-display>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Lawan</label>
        <input type="text" class="form-control" value="{{ $lineupList->opponent()?->name ?? '-' }}" readonly data-lineup-opponent>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Label Jadwal</label>
        <input type="text" class="form-control" value="{{ old('match_day', $lineupList->match_day) }}" readonly data-lineup-matchday>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Lokasi</label>
        <input type="text" class="form-control" value="{{ old('played_at', $lineupList->played_at) }}" readonly data-lineup-venue>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tanggal</label>
        <input type="text" class="form-control" value="{{ old('match_date', optional($lineupList->match_date)->format('d M Y')) }}" readonly data-lineup-date>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Jam Kickoff</label>
        <input type="text" class="form-control" value="{{ old('played_time', optional($lineupList->played_time)->format('H:i')) }}" readonly data-lineup-kickoff>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Judul DSP</label>
        <div class="form-control-plaintext">
            {{ $lineupList->title ?: 'Otomatis berdasarkan pertandingan dan lawan.' }}
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Pelatih</label>
        <input type="text" name="coach_name" class="form-control" value="{{ old('coach_name', $lineupList->coach_name) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Warna Jersey</label>
        <input type="text" name="jersey_color" class="form-control" value="{{ old('jersey_color', $lineupList->jersey_color) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Warna Jersey Kiper</label>
        <input type="text" name="goalkeeper_jersey_color" class="form-control" value="{{ old('goalkeeper_jersey_color', $lineupList->goalkeeper_jersey_color) }}">
    </div>
    <div class="col-lg-12 mb-3">
        <div class="alert alert-light border mb-0">
            Detail lawan, lokasi, tanggal, dan jam pertandingan mengikuti jadwal resmi yang dibuat admin. Klub hanya memilih pertandingan lalu menyusun DSP.
        </div>
    </div>
    <div class="col-lg-12 mb-3">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $lineupList->notes) }}</textarea>
    </div>
</div>

<div class="card border mt-3 lineup-roster-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3 lineup-section-header">
            <div>
                <h5 class="mb-1">Susunan DSP</h5>
                <div class="text-muted">Pilih peran pemain satu kali. Susunan DSP otomatis mengikuti urutan di panel Starter dan Cadangan, lalu bisa dirapikan dengan tombol naik atau turun.</div>
            </div>
            <div class="text-muted small"><span data-lineup-count>0</span> pemain tersedia</div>
        </div>

        <div class="alert alert-light border mb-3" data-lineup-guide>
            Pilih pertandingan terlebih dahulu untuk menampilkan daftar pemain sesuai klub dan kelompok usia.
        </div>

        <div class="alert alert-info mb-3">
            Aturan DSP: tepat {{ \App\Models\LineupList::REQUIRED_STARTERS }} starter dan maksimal {{ \App\Models\LineupList::MAX_SUBSTITUTES }} cadangan. Jika pemain tersedia 16 orang, cukup pilih 11 starter lalu sisanya bisa dijadikan cadangan.
        </div>

        @if ($blockedSelectedPlayers->isNotEmpty())
            <div class="alert alert-danger mb-3">
                Beberapa pemain yang sebelumnya ada di DSP sekarang tidak bisa dipakai karena status verifikasinya belum diterima:
                <strong>{{ $blockedSelectedPlayers->pluck('name')->implode(', ') }}</strong>.
                Perbaiki data pemain tersebut lalu ajukan ulang sampai statusnya diterima admin.
            </div>
        @endif

        @if ($blockedLineupPlayers->isNotEmpty())
            <div class="alert alert-warning mb-3">
                Pemain dengan status belum diterima admin tidak bisa dimasukkan ke DSP.
                Perbaiki data pemain berikut lalu ajukan ulang untuk verifikasi:
                <strong>{{ $blockedLineupPlayers->pluck('name')->implode(', ') }}</strong>.
            </div>
        @endif

        <div class="alert alert-secondary border mb-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2" data-lineup-progress>
            <div>
                <div class="fw-semibold" data-lineup-progress-title>Starter 0/{{ \App\Models\LineupList::REQUIRED_STARTERS }} · Cadangan 0/{{ \App\Models\LineupList::MAX_SUBSTITUTES }}</div>
                <div class="small text-muted" data-lineup-progress-text>Pilih tepat {{ \App\Models\LineupList::REQUIRED_STARTERS }} starter. Pemain yang sudah dipilih tidak akan muncul di dua kondisi sekaligus.</div>
            </div>
        </div>

        <div class="row g-4" data-lineup-builder>
            <div class="col-xl-7">
                <div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3 lineup-section-header">
                        <div>
                            <h6 class="mb-1">Daftar Pemain</h6>
                            <div class="text-muted small">Klik `Starter` atau `Cadangan`. Jika belum dipakai, biarkan `Belum dipilih`.</div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2" data-lineup-available>
                        @foreach ($lineupPlayers as $player)
                            @php
                                $initialRole = in_array($player->id, $starterIds, true)
                                    ? \App\Models\LineupList::ROLE_STARTER
                                    : (in_array($player->id, $substituteIds, true) ? \App\Models\LineupList::ROLE_SUBSTITUTE : '');
                                $ageDetails = $player->ageRegistrations
                                    ->mapWithKeys(fn ($registration) => [
                                        $registration->age_group_id => [
                                            'jersey' => $registration->jersey_number ?: ($player->jersey_number ?: '-'),
                                            'position' => $registration->position ?: ($player->position ?: 'Tanpa posisi'),
                                        ],
                                    ])
                                    ->all();
                            @endphp
                            <div
                                class="border rounded p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
                                data-player-option
                                data-player-id="{{ $player->id }}"
                                data-player-name="{{ $player->name }}"
                                data-player-club="{{ $player->club_id }}"
                                data-player-ages="{{ $player->ageRegistrations->pluck('age_group_id')->implode(',') }}"
                                data-player-default-jersey="{{ $player->jersey_number ?: '-' }}"
                                data-player-default-position="{{ $player->position ?: 'Tanpa posisi' }}"
                                data-player-status="{{ auth()->user()->isAdmin() ? ucfirst($player->verification_status) : '' }}"
                                data-initial-role="{{ $initialRole }}"
                                data-player-age-details='@json($ageDetails)'
                            >
                                <div>
                                    <div class="fw-semibold lineup-player-name">{{ $player->name }}</div>
                                    <div class="text-muted small lineup-player-meta" data-player-meta>
                                        #{{ $player->displayJerseyNumber($selectedAgeGroupId) ?: '-' }} · {{ $player->displayPosition($selectedAgeGroupId) ?: 'Tanpa posisi' }}
                                        @if (auth()->user()->isAdmin())
                                            · {{ ucfirst($player->verification_status) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm lineup-role-toggle" role="group" aria-label="Pilih role">
                                    <button type="button" class="btn btn-outline-secondary" data-role-button="" data-role-value="">Belum dipilih</button>
                                    <button type="button" class="btn btn-outline-primary" data-role-button="starter" data-role-value="{{ \App\Models\LineupList::ROLE_STARTER }}">Starter</button>
                                    <button type="button" class="btn btn-outline-warning" data-role-button="substitute" data-role-value="{{ \App\Models\LineupList::ROLE_SUBSTITUTE }}">Cadangan</button>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-muted small" data-lineup-empty-available hidden>Tidak ada pemain yang cocok untuk filter ini.</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="d-flex flex-column gap-3 h-100">
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3 lineup-section-header">
                            <div>
                                <h6 class="mb-1">Starter</h6>
                                <div class="text-muted small">Harus tepat {{ \App\Models\LineupList::REQUIRED_STARTERS }} pemain.</div>
                            </div>
                            <span class="badge text-bg-primary"><span data-starter-count>0</span>/{{ \App\Models\LineupList::REQUIRED_STARTERS }}</span>
                        </div>
                        <div class="d-flex flex-column gap-2" data-lineup-starters></div>
                        <div class="text-muted small" data-lineup-empty-starters>Belum ada starter dipilih.</div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3 lineup-section-header">
                            <div>
                                <h6 class="mb-1">Cadangan</h6>
                                <div class="text-muted small">Maksimal {{ \App\Models\LineupList::MAX_SUBSTITUTES }} pemain.</div>
                            </div>
                            <span class="badge text-bg-warning"><span data-substitute-count>0</span>/{{ \App\Models\LineupList::MAX_SUBSTITUTES }}</span>
                        </div>
                        <div class="d-flex flex-column gap-2" data-lineup-substitutes></div>
                        <div class="text-muted small" data-lineup-empty-substitutes>Belum ada cadangan dipilih.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const header = document.querySelector('[data-lineup-form]');
        if (!header) return;

        const root = header.closest('form') || document;
        const matchInput = header.querySelector('[data-lineup-match]');
        const clubInput = header.querySelector('[data-lineup-club]');
        const ageInput = header.querySelector('[data-lineup-age]');
        const clubDisplay = header.querySelector('[data-lineup-club-display]');
        const clubReadonlyDisplay = header.querySelector('[data-lineup-club-readonly]');
        const ageDisplay = header.querySelector('[data-lineup-age-display]');
        const opponentDisplay = header.querySelector('[data-lineup-opponent]');
        const matchdayDisplay = header.querySelector('[data-lineup-matchday]');
        const venueDisplay = header.querySelector('[data-lineup-venue]');
        const dateDisplay = header.querySelector('[data-lineup-date]');
        const kickoffDisplay = header.querySelector('[data-lineup-kickoff]');
        const clubHelp = header.querySelector('[data-lineup-club-help]');
        const countNode = root.querySelector('[data-lineup-count]');
        const guideNode = root.querySelector('[data-lineup-guide]');
        const starterList = root.querySelector('[data-lineup-starters]');
        const substituteList = root.querySelector('[data-lineup-substitutes]');
        const starterCountNode = root.querySelector('[data-starter-count]');
        const substituteCountNode = root.querySelector('[data-substitute-count]');
        const starterEmptyNode = root.querySelector('[data-lineup-empty-starters]');
        const substituteEmptyNode = root.querySelector('[data-lineup-empty-substitutes]');
        const availableEmptyNode = root.querySelector('[data-lineup-empty-available]');
        const progressTitleNode = root.querySelector('[data-lineup-progress-title]');
        const progressTextNode = root.querySelector('[data-lineup-progress-text]');
        const progressNode = root.querySelector('[data-lineup-progress]');
        const requiredStarters = {{ \App\Models\LineupList::REQUIRED_STARTERS }};
        const maxSubstitutes = {{ \App\Models\LineupList::MAX_SUBSTITUTES }};
        const draftKey = `lap-dashboard:lineup-form-draft:${window.location.pathname}`;
        const currentLineupClubId = clubInput?.dataset.currentClubId || '';
        let isRestoringDraft = false;
        let saveDraftTimer = null;

        const selectedMatchOption = () => matchInput?.selectedOptions?.[0] || null;

        const syncMatchDetails = () => {
            const option = selectedMatchOption();
            const hasMatch = option && option.value;

            if (!hasMatch) {
                ageInput.value = '';
                if (ageDisplay) ageDisplay.value = '-';
                if (opponentDisplay) opponentDisplay.value = '-';
                if (matchdayDisplay) matchdayDisplay.value = '';
                if (venueDisplay) venueDisplay.value = '';
                if (dateDisplay) dateDisplay.value = '';
                if (kickoffDisplay) kickoffDisplay.value = '';
                return;
            }

            ageInput.value = option.dataset.ageId || '';
            if (ageDisplay) ageDisplay.value = option.dataset.ageName || '-';
            if (matchdayDisplay) matchdayDisplay.value = option.dataset.matchDay || '';
            if (venueDisplay) venueDisplay.value = option.dataset.venue || '';
            if (dateDisplay) dateDisplay.value = option.dataset.date || '';
            if (kickoffDisplay) kickoffDisplay.value = option.dataset.kickoff || '';

            const clubAId = option.dataset.clubAId || '';
            const clubBId = option.dataset.clubBId || '';
            const clubAName = option.dataset.clubAName || '';
            const clubBName = option.dataset.clubBName || '';
            const usedClubIds = (option.dataset.usedClubIds || '').split(',').filter(Boolean);
            const availableClubIds = [clubAId, clubBId].filter((id) => id && (!usedClubIds.includes(id) || id === currentLineupClubId));
            const availableClubNames = [
                (!usedClubIds.includes(clubAId) || clubAId === currentLineupClubId) ? clubAName : null,
                (!usedClubIds.includes(clubBId) || clubBId === currentLineupClubId) ? clubBName : null,
            ].filter(Boolean);

            if (clubInput.tagName === 'SELECT') {
                Array.from(clubInput.options).forEach((clubOption) => {
                    if (!clubOption.value) return;
                    const enabled =
                        (clubOption.value === clubAId || clubOption.value === clubBId)
                        && (!usedClubIds.includes(clubOption.value) || clubOption.value === currentLineupClubId);
                    clubOption.disabled = !enabled;
                });

                const selectedClubIsUsable =
                    (clubInput.value === clubAId || clubInput.value === clubBId)
                    && (!usedClubIds.includes(clubInput.value) || clubInput.value === currentLineupClubId);
                const availableClubId = availableClubIds[0] || '';

                if (!selectedClubIsUsable) {
                    clubInput.value = availableClubId;
                }

                if (clubReadonlyDisplay) {
                    const selectedOption = clubInput.selectedOptions[0];
                    clubReadonlyDisplay.value = selectedOption?.textContent?.trim() || availableClubNames[0] || '';
                    clubReadonlyDisplay.classList.toggle('d-none', availableClubIds.length !== 1);
                    clubInput.classList.toggle('d-none', availableClubIds.length === 1);
                }
            } else if (clubInput.type === 'hidden') {
                if (clubInput.value !== clubAId && clubInput.value !== clubBId) {
                    clubInput.value = clubAId || clubBId || clubInput.value;
                }

                if (clubDisplay) {
                    clubDisplay.value = clubInput.value === clubBId ? clubBName : clubAName;
                }
            }

            const currentClubId = clubInput.value;
            if (opponentDisplay) {
                opponentDisplay.value = currentClubId === clubAId ? clubBName : clubAName;
            }

            if (clubHelp) {
                if (clubInput.tagName === 'SELECT') {
                    clubHelp.textContent = availableClubNames.length === 1
                        ? `Klub penyusun DSP otomatis dikunci ke ${availableClubNames[0]} karena klub lainnya sudah memiliki DSP.`
                        : availableClubNames.length > 0
                        ? `Klub yang masih bisa menyusun DSP untuk match ini: ${availableClubNames.join(', ')}.`
                        : 'Kedua klub pada pertandingan ini sudah memiliki DSP.';
                } else {
                    clubHelp.textContent = 'Klub penyusun DSP mengikuti akun club yang sedang login.';
                }
            }
        };

        const jerseyOverrides = @json($selectedJerseys);

        const getPlayerMeta = (option, playerId = null) => {
            const ageId = ageInput.value;
            const details = JSON.parse(option.dataset.playerAgeDetails || '{}');
            const selectedDetail = ageId && details[ageId] ? details[ageId] : null;
            const override = playerId && jerseyOverrides[playerId] ? jerseyOverrides[playerId] : null;

            return {
                jersey: override || selectedDetail?.jersey || option.dataset.playerDefaultJersey || '-',
                position: selectedDetail?.position || option.dataset.playerDefaultPosition || 'Tanpa posisi',
            };
        };

        const refreshPlayerMeta = () => {
            root.querySelectorAll('[data-player-option]').forEach((option) => {
                const metaNode = option.querySelector('[data-player-meta]');
                if (!metaNode) return;

                const playerId = Number(option.dataset.playerId);
                const meta = getPlayerMeta(option, playerId);
                const statusText = option.dataset.playerStatus ? ` · ${option.dataset.playerStatus}` : '';
                metaNode.textContent = `#${meta.jersey} · ${meta.position}${statusText}`;
            });
        };

        const selectedPlayers = {
            starter: @json($orderedStarterIds),
            substitute: @json($orderedSubstituteIds),
        };

        const draftFieldNames = [
            'match_id',
            'club_id',
            'coach_name',
            'jersey_color',
            'goalkeeper_jersey_color',
            'notes',
        ];

        const isVisibleForSelection = (option, clubId, ageId) => {
            return !!(
                clubId
                && ageId
                && option.dataset.playerClub === clubId
                && (option.dataset.playerAges || '').split(',').includes(ageId)
            );
        };

        const syncOptionRoleState = (option) => {
            const playerId = Number(option.dataset.playerId);
            const role = selectedPlayers.starter.includes(playerId)
                ? 'starter'
                : (selectedPlayers.substitute.includes(playerId) ? 'substitute' : '');

            option.dataset.currentRole = role;
            setRoleButtonsState(option, role);
        };

        const sanitizeSelectedPlayers = (clubId, ageId) => {
            ['starter', 'substitute'].forEach((role) => {
                const seen = new Set();

                selectedPlayers[role] = selectedPlayers[role].filter((playerId) => {
                    if (seen.has(playerId)) {
                        return false;
                    }

                    seen.add(playerId);

                    const option = root.querySelector(`[data-player-id="${playerId}"]`);

                    return !!option && isVisibleForSelection(option, clubId, ageId);
                });
            });
        };

        const persistDraft = () => {
            if (isRestoringDraft) return;

            const fields = {};

            draftFieldNames.forEach((name) => {
                const field = root.querySelector(`[name="${name}"]`);
                if (field) {
                    fields[name] = field.value;
                }
            });

            localStorage.setItem(draftKey, JSON.stringify({
                fields,
                selectedPlayers: {
                    starter: [...selectedPlayers.starter],
                    substitute: [...selectedPlayers.substitute],
                },
                jerseyOverrides: { ...jerseyOverrides },
                savedAt: new Date().toISOString(),
            }));
        };

        const saveDraft = ({ immediate = false } = {}) => {
            if (isRestoringDraft) return;

            window.clearTimeout(saveDraftTimer);

            if (immediate) {
                persistDraft();
                return;
            }

            saveDraftTimer = window.setTimeout(() => {
                persistDraft();
            }, 250);
        };

        const restoreDraft = () => {
            const rawDraft = localStorage.getItem(draftKey);
            if (!rawDraft) return;

            try {
                const draft = JSON.parse(rawDraft);
                isRestoringDraft = true;

                Object.entries(draft.fields || {}).forEach(([name, value]) => {
                    const field = root.querySelector(`[name="${name}"]`);
                    if (field) {
                        field.value = value ?? '';
                    }
                });

                syncMatchDetails();

                if (Array.isArray(draft.selectedPlayers?.starter)) {
                    selectedPlayers.starter = draft.selectedPlayers.starter.map(Number).filter(Boolean);
                }

                if (Array.isArray(draft.selectedPlayers?.substitute)) {
                    selectedPlayers.substitute = draft.selectedPlayers.substitute.map(Number).filter(Boolean);
                }

                Object.keys(jerseyOverrides).forEach((key) => delete jerseyOverrides[key]);
                Object.entries(draft.jerseyOverrides || {}).forEach(([playerId, value]) => {
                    if (value !== null && value !== '') {
                        jerseyOverrides[playerId] = value;
                    }
                });
            } catch (error) {
                localStorage.removeItem(draftKey);
            } finally {
                isRestoringDraft = false;
            }
        };

        const setRoleButtonsState = (option, role) => {
            option.querySelectorAll('[data-role-button]').forEach((button) => {
                const isActive = button.dataset.roleValue === role;
                button.classList.toggle('active', isActive);
                button.classList.toggle('btn-secondary', isActive && role === '');
                button.classList.toggle('btn-outline-secondary', !isActive || role !== '');
                button.classList.toggle('btn-primary', isActive && role === 'starter');
                button.classList.toggle('btn-outline-primary', !isActive || role !== 'starter');
                button.classList.toggle('btn-warning', isActive && role === 'substitute');
                button.classList.toggle('btn-outline-warning', !isActive || role !== 'substitute');
            });
        };

        const refreshAvailableActions = () => {
            root.querySelectorAll('[data-player-option]').forEach((option) => {
                const currentRole = option.dataset.currentRole || '';
                const starterButton = option.querySelector('[data-role-value="starter"]');
                const substituteButton = option.querySelector('[data-role-value="substitute"]');

                if (starterButton) {
                    starterButton.disabled = currentRole !== 'starter' && selectedPlayers.starter.length >= requiredStarters;
                }

                if (substituteButton) {
                    substituteButton.disabled = currentRole !== 'substitute' && selectedPlayers.substitute.length >= maxSubstitutes;
                }
            });
        };

        const movePlayer = (role, index, direction) => {
            const nextIndex = index + direction;
            const list = selectedPlayers[role];
            if (nextIndex < 0 || nextIndex >= list.length) return;

            [list[index], list[nextIndex]] = [list[nextIndex], list[index]];
            renderSelectedLists();
        };

        const renderSelectionItem = (playerId, role, index) => {
            const option = root.querySelector(`[data-player-id="${playerId}"]`);
            if (!option) return null;

            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-2 d-flex justify-content-between align-items-center gap-3 lineup-selected-player';

            const meta = getPlayerMeta(option, playerId);
            const statusText = option.dataset.playerStatus ? ` · ${option.dataset.playerStatus}` : '';
            wrapper.innerHTML = `
                <div class="d-flex align-items-start gap-2">
                    <span class="badge text-bg-light border">${index + 1}</span>
                    <div>
                        <div class="fw-semibold lineup-selected-player-name">${option.dataset.playerName}</div>
                        <div class="text-muted small lineup-selected-player-meta" data-lineup-meta>#${meta.jersey} · ${meta.position}${statusText}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 lineup-selected-player-actions">
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <span class="input-group-text">No Jersey</span>
                        <input type="text" class="form-control" data-jersey-input value="${meta.jersey === '-' ? '' : meta.jersey}">
                    </div>
                    <button type="button" class="btn btn-sm btn-light" data-move="-1">↑</button>
                    <button type="button" class="btn btn-sm btn-light" data-move="1">↓</button>
                </div>
            `;

            wrapper.querySelector('[data-move="-1"]').addEventListener('click', () => movePlayer(role, index, -1));
            wrapper.querySelector('[data-move="1"]').addEventListener('click', () => movePlayer(role, index, 1));

            const jerseyInput = wrapper.querySelector('[data-jersey-input]');
            const metaNode = wrapper.querySelector('[data-lineup-meta]');
            if (jerseyInput && metaNode) {
                jerseyInput.addEventListener('input', () => {
                    const value = jerseyInput.value.trim();
                    if (value) {
                        jerseyOverrides[playerId] = value;
                    } else {
                        delete jerseyOverrides[playerId];
                    }
                    const updated = getPlayerMeta(option, playerId);
                    metaNode.textContent = `#${updated.jersey} · ${updated.position}${statusText}`;
                    refreshPlayerMeta();
                    saveDraft({ immediate: true });
                });
            }

            const playerInput = document.createElement('input');
            playerInput.type = 'hidden';
            playerInput.name = `${role}_player_ids[]`;
            playerInput.value = playerId;
            wrapper.appendChild(playerInput);

            const orderInput = document.createElement('input');
            orderInput.type = 'hidden';
            orderInput.name = `${role}_orders[${playerId}]`;
            orderInput.value = index + 1;
            wrapper.appendChild(orderInput);

            const jerseyHidden = document.createElement('input');
            jerseyHidden.type = 'hidden';
            jerseyHidden.name = `${role}_jerseys[${playerId}]`;
            jerseyHidden.value = meta.jersey === '-' ? '' : meta.jersey;
            wrapper.appendChild(jerseyHidden);

            if (jerseyInput) {
                jerseyInput.addEventListener('input', () => {
                    jerseyHidden.value = jerseyInput.value.trim();
                    saveDraft({ immediate: true });
                });
            }

            return wrapper;
        };

        const renderSelectedLists = () => {
            starterList.innerHTML = '';
            substituteList.innerHTML = '';

            selectedPlayers.starter.forEach((playerId, index) => {
                const item = renderSelectionItem(playerId, 'starter', index);
                if (item) starterList.appendChild(item);
            });

            selectedPlayers.substitute.forEach((playerId, index) => {
                const item = renderSelectionItem(playerId, 'substitute', index);
                if (item) substituteList.appendChild(item);
            });

            starterCountNode.textContent = selectedPlayers.starter.length;
            substituteCountNode.textContent = selectedPlayers.substitute.length;
            starterEmptyNode.hidden = selectedPlayers.starter.length > 0;
            substituteEmptyNode.hidden = selectedPlayers.substitute.length > 0;
            refreshAvailableActions();

            if (progressTitleNode) {
                progressTitleNode.textContent = `Starter ${selectedPlayers.starter.length}/${requiredStarters} · Cadangan ${selectedPlayers.substitute.length}/${maxSubstitutes}`;
            }

            if (progressTextNode) {
                if (selectedPlayers.starter.length < requiredStarters) {
                    progressTextNode.textContent = `Tambahkan ${requiredStarters - selectedPlayers.starter.length} starter lagi. Pemain yang sudah dipilih hanya masuk ke satu kelompok.`;
                } else if (selectedPlayers.starter.length > requiredStarters) {
                    progressTextNode.textContent = `Starter melebihi batas. Kurangi sampai tepat ${requiredStarters} pemain.`;
                } else if (selectedPlayers.substitute.length > maxSubstitutes) {
                    progressTextNode.textContent = `Cadangan melebihi batas. Maksimal ${maxSubstitutes} pemain.`;
                } else {
                    progressTextNode.textContent = 'Jumlah starter sudah pas. Lanjutkan isi cadangan bila diperlukan atau simpan DSP.';
                }
            }

            if (progressNode) {
                progressNode.classList.remove('alert-secondary', 'alert-success', 'alert-warning');
                progressNode.classList.add(
                    selectedPlayers.starter.length === requiredStarters && selectedPlayers.substitute.length <= maxSubstitutes
                        ? 'alert-success'
                        : 'alert-warning'
                );
            }
        };

        const setPlayerRole = (playerId, role) => {
            ['starter', 'substitute'].forEach((key) => {
                selectedPlayers[key] = selectedPlayers[key].filter((id) => id !== playerId);
            });

            if (role === 'starter') {
                selectedPlayers.starter.push(playerId);
            } else if (role === 'substitute') {
                selectedPlayers.substitute.push(playerId);
            }

            const option = root.querySelector(`[data-player-id="${playerId}"]`);
            if (option) {
                option.dataset.currentRole = role;
                setRoleButtonsState(option, role);
            }

            renderSelectedLists();
            saveDraft({ immediate: true });
        };

        const refreshOptions = () => {
            syncMatchDetails();

            const clubId = clubInput.value;
            const ageId = ageInput.value;
            let visibleCount = 0;

            sanitizeSelectedPlayers(clubId, ageId);

            refreshPlayerMeta();

            root.querySelectorAll('[data-player-option]').forEach((option) => {
                const isVisible = isVisibleForSelection(option, clubId, ageId);

                option.hidden = !isVisible;

                syncOptionRoleState(option);

                if (!isVisible) return;

                visibleCount += 1;
            });

            if (guideNode) guideNode.hidden = !!(clubId && ageId);
            if (countNode) countNode.textContent = visibleCount;
            if (availableEmptyNode) availableEmptyNode.hidden = visibleCount > 0 || !clubId || !ageId;
            renderSelectedLists();
        };

        restoreDraft();

        root.querySelectorAll('[data-player-option]').forEach((option) => {
            const playerId = Number(option.dataset.playerId);
            const initialRole = selectedPlayers.starter.includes(playerId)
                ? 'starter'
                : (selectedPlayers.substitute.includes(playerId) ? 'substitute' : (option.dataset.initialRole || ''));
            option.dataset.currentRole = initialRole;
            setRoleButtonsState(option, initialRole);

            option.querySelectorAll('[data-role-button]').forEach((button) => {
                button.addEventListener('click', () => {
                    setPlayerRole(playerId, button.dataset.roleValue);
                });
            });
        });

        matchInput?.addEventListener('change', () => {
            refreshOptions();
            saveDraft();
        });
        clubInput.addEventListener('change', () => {
            refreshOptions();
            saveDraft();
        });
        draftFieldNames.forEach((name) => {
            root.querySelector(`[name="${name}"]`)?.addEventListener('input', saveDraft);
        });
        root.addEventListener('submit', () => {
            window.clearTimeout(saveDraftTimer);
            localStorage.removeItem(draftKey);
        });
        window.addEventListener('pagehide', persistDraft);
        window.addEventListener('beforeunload', persistDraft);
        refreshOptions();
    })();
</script>
