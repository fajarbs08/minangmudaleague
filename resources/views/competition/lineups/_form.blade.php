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
    $selectedAgeGroup = $ageGroups->firstWhere('id', (int) $selectedAgeGroupId) ?: $lineupList->ageGroup;
    $initialRequiredStarters = \App\Models\LineupList::requiredStartersForAgeGroup($selectedAgeGroup);
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
                    data-coach-a-name="{{ $match->coach_a_name }}"
                    data-coach-b-name="{{ $match->coach_b_name }}"
                    data-used-club-ids="{{ $usedClubIds->implode(',') }}"
                    data-age-id="{{ $match->age_group_id }}"
                    data-age-name="{{ $match->ageGroup?->name }}"
                    data-required-starters="{{ \App\Models\LineupList::requiredStartersForAgeGroup($match->ageGroup) }}"
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
        <label class="form-label">Tempat Pertandingan</label>
        <input type="text" name="played_at" class="form-control" value="{{ old('played_at', $lineupList->played_at) }}" data-lineup-venue>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tanggal Pertandingan</label>
        <input type="date" name="match_date" class="form-control {{ !auth()->user()->isAdmin() ? 'bg-light' : '' }}" value="{{ old('match_date', optional($lineupList->match_date)->format('Y-m-d')) }}" data-lineup-date {{ !auth()->user()->isAdmin() ? 'readonly style=pointer-events:none;' : '' }}>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Jam Kickoff</label>
        <input type="time" name="played_time" class="form-control {{ !auth()->user()->isAdmin() ? 'bg-light' : '' }}" value="{{ old('played_time', optional($lineupList->played_time)->format('H:i')) }}" data-lineup-kickoff {{ !auth()->user()->isAdmin() ? 'readonly style=pointer-events:none;' : '' }}>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Judul DSP</label>
        <div class="form-control-plaintext">
            {{ $lineupList->title ?: 'Otomatis berdasarkan pertandingan dan lawan.' }}
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Nama Pelatih</label>
        <input type="text" name="coach_name" class="form-control {{ !auth()->user()->isAdmin() ? 'bg-light' : '' }}" value="{{ old('coach_name', $lineupList->coach_name) }}" {{ !auth()->user()->isAdmin() ? 'readonly' : '' }} data-lineup-coach>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Warna Jersey Utama</label>
        <input type="text" name="jersey_color" class="form-control" value="{{ old('jersey_color', $lineupList->jersey_color) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Warna Jersey Kiper</label>
        <input type="text" name="goalkeeper_jersey_color" class="form-control" value="{{ old('goalkeeper_jersey_color', $lineupList->goalkeeper_jersey_color) }}">
    </div>
    <div class="col-lg-12 mb-3">
        <div class="alert alert-light border mb-0">
            @if(auth()->user()->isAdmin())
                Tim mengisi Warna Jersey Utama, Warna Jersey Kiper, Tempat Pertandingan, Tanggal Pertandingan, dan Jam Kickoff. Nilai awal tetap mengikuti jadwal resmi saat pertandingan dipilih.
            @else
                Tim hanya mengisi Warna Jersey Utama, Warna Jersey Kiper, dan Tempat Pertandingan. Nilai Tanggal, Jam Kickoff, Pelatih, dan Catatan dikunci oleh admin.
            @endif
        </div>
    </div>
    <div class="col-lg-12 mb-3">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="3" class="form-control {{ !auth()->user()->isAdmin() ? 'bg-light' : '' }}" {{ !auth()->user()->isAdmin() ? 'readonly' : '' }}>{{ old('notes', $lineupList->notes) }}</textarea>
    </div>
</div>

<script>
    (() => {
        const header = document.querySelector('[data-lineup-form]');
        if (!header) return;

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
        const coachInput = header.querySelector('[data-lineup-coach]');
        const clubHelp = header.querySelector('[data-lineup-club-help]');
        const currentLineupClubId = clubInput?.dataset.currentClubId || '';
        let previousSyncedMatchId = '';

        const syncEditableField = (field, value, force = false) => {
            if (!field) return;
            if (force || !field.value) {
                field.value = value || '';
            }
        };

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
                previousSyncedMatchId = '';
                return;
            }

            const matchChanged = option.value !== previousSyncedMatchId;

            ageInput.value = option.dataset.ageId || '';

            if (ageDisplay) ageDisplay.value = option.dataset.ageName || '-';
            if (matchdayDisplay) matchdayDisplay.value = option.dataset.matchDay || '';
            syncEditableField(venueDisplay, option.dataset.venue || '', matchChanged);
            syncEditableField(dateDisplay, option.dataset.dateValue || '', matchChanged);
            syncEditableField(kickoffDisplay, option.dataset.kickoff || '', matchChanged);
            previousSyncedMatchId = option.value;

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

            const coachAName = option.dataset.coachAName || '';
            const coachBName = option.dataset.coachBName || '';
            const coachName = currentClubId === clubAId ? coachAName : coachBName;
            if (coachInput) {
                syncEditableField(coachInput, coachName, matchChanged || !coachInput.value);
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

        matchInput?.addEventListener('change', () => {
            syncMatchDetails();
        });
        clubInput?.addEventListener('change', () => {
            syncMatchDetails();
        });

        syncMatchDetails();
    })();
</script>
