@php
    $eliminatedClubIdsByAgeGroup = $knockoutEliminatedClubIdsByAgeGroup ?? [];
    $knockoutSourceOptionsByAgeGroup = $knockoutSourceOptionsByAgeGroup ?? [];
    $currentMatchClubIds = collect($currentMatchClubIds ?? [])->map(fn ($id) => (int) $id)->values()->all();
    $kickoffValue = old('kickoff_time', optional($matchSchedule->kickoff_time)->format('H:i'));
    [$kickoffHour, $kickoffMinute] = str_contains((string) $kickoffValue, ':')
        ? explode(':', $kickoffValue, 2)
        : [null, null];
    $knockoutRoundLabel = old('round_label', $matchSchedule->round_label);
    $knockoutRoundOrder = old('round_order', $matchSchedule->round_order);
    $knockoutBracketSlot = old('bracket_slot', $matchSchedule->bracket_slot);
    $knockoutSourceMatchAId = old('source_match_a_id', $matchSchedule->source_match_a_id);
    $knockoutSourceMatchBId = old('source_match_b_id', $matchSchedule->source_match_b_id);
@endphp

<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kelompok Usia <span class="text-danger">*</span></label>
        <select name="age_group_id" class="form-select" data-age-group-select required>
            <option value="">Pilih kelompok usia</option>
            @foreach ($ageGroups as $ageGroup)
                <option value="{{ $ageGroup->id }}" @selected((string) old('age_group_id', $matchSchedule->age_group_id) === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Format Kompetisi <span class="text-danger">*</span></label>
        <select name="competition_format" class="form-select" data-competition-format required>
            @foreach ($formatOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('competition_format', $matchSchedule->competition_format ?? 'league') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">`Liga` untuk klasemen, `Knockout` untuk bagan babak gugur.</div>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Klub A <span class="text-danger">*</span></label>
        <select name="club_a_id" class="form-select" data-club-select required>
            <option value="">Pilih klub</option>
            @foreach ($clubs as $club)
                <option value="{{ $club->id }}" @selected((string) old('club_a_id', $matchSchedule->club_a_id) === (string) $club->id)>{{ $club->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Klub B <span class="text-danger">*</span></label>
        <select name="club_b_id" class="form-select" data-club-select required>
            <option value="">Pilih klub</option>
            @foreach ($clubs as $club)
                <option value="{{ $club->id }}" @selected((string) old('club_b_id', $matchSchedule->club_b_id) === (string) $club->id)>{{ $club->name }}</option>
            @endforeach
        </select>
        <div class="form-text" data-knockout-club-hint hidden>Klub yang sudah gugur di knockout untuk kelompok usia ini otomatis tidak bisa dipilih.</div>
    </div>
    <div class="col-12 knockout-field">
        <div class="rounded-3 border bg-light-subtle p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="small text-uppercase fw-semibold text-muted">Posisi Bracket</div>
                    <div class="fw-semibold mt-1" data-knockout-position-copy>
                        @if (filled($knockoutRoundOrder) && filled($knockoutBracketSlot))
                            {{ $knockoutRoundLabel ?: 'Babak '.$knockoutRoundOrder }} • Urutan {{ $knockoutRoundOrder }} • Slot {{ $knockoutBracketSlot }}
                        @else
                            Pilih slot dari board knockout.
                        @endif
                    </div>
                </div>
                <span class="badge bg-warning-subtle text-warning" data-knockout-slot-badge>
                    @if (filled($knockoutBracketSlot))
                        Slot {{ $knockoutBracketSlot }}
                    @else
                        Posisi belum dipilih
                    @endif
                </span>
            </div>
        </div>
        <input type="hidden" name="round_order" value="{{ $knockoutRoundOrder }}" data-round-order-input>
        <input type="hidden" name="bracket_slot" value="{{ $knockoutBracketSlot }}" data-bracket-slot-input>
    </div>
    <div class="col-lg-4 mb-3 knockout-field">
        <label class="form-label">Label Babak <span class="text-danger">*</span></label>
        <input
            type="text"
            name="round_label"
            class="form-control"
            data-round-label-input
            value="{{ $knockoutRoundLabel }}"
            placeholder="Contoh: Semifinal"
        >
        <div class="form-text">Nama babak yang tampil di board dan laporan bracket.</div>
    </div>
    <div class="col-lg-6 mb-3 knockout-field">
        <label class="form-label">Sumber Tim A</label>
        <select name="source_match_a_id" class="form-select" data-source-match-a-select data-selected-value="{{ $knockoutSourceMatchAId }}">
            <option value="">Tim A ditentukan manual / belum ada sumber</option>
        </select>
        <div class="form-text">Pilih pertandingan babak sebelumnya yang menghasilkan peserta untuk posisi Tim A.</div>
    </div>
    <div class="col-lg-6 mb-3 knockout-field">
        <label class="form-label">Sumber Tim B</label>
        <select name="source_match_b_id" class="form-select" data-source-match-b-select data-selected-value="{{ $knockoutSourceMatchBId }}">
            <option value="">Tim B ditentukan manual / belum ada sumber</option>
        </select>
        <div class="form-text">Pilih pertandingan babak sebelumnya yang menghasilkan peserta untuk posisi Tim B.</div>
    </div>
    <div class="col-12 knockout-field d-none" data-knockout-board-alert>
        <div class="alert alert-warning border border-warning-subtle mb-0">
            Posisi bracket knockout dipilih dari halaman <strong>Jadwal Match Knockout</strong>. Pilih slot di board dulu, lalu isi detail match di form ini.
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Label Jadwal <span class="text-danger">*</span></label>
        <input type="text" name="match_day" class="form-control" value="{{ old('match_day', $matchSchedule->match_day) }}" placeholder="Contoh: Pekan 1, Putaran 3, Semifinal 1" required>
        <div class="form-text">Bukan nama hari kalender. Kolom ini dipakai untuk label matchday atau nama sesi pertandingan.</div>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
        <input
            type="date"
            name="match_date"
            class="form-control"
            value="{{ old('match_date', optional($matchSchedule->match_date)->format('Y-m-d')) }}"
            data-native-picker
            required
        >
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Jam Kickoff <span class="text-danger">*</span></label>
        <input type="hidden" name="kickoff_time" value="{{ $kickoffValue }}" data-kickoff-hidden required>
        <div class="row g-2" data-kickoff-picker>
            <div class="col-6">
                <select class="form-select" data-kickoff-hour required>
                    <option value="">Jam</option>
                    @foreach (range(0, 23) as $hour)
                        @php($hourValue = str_pad((string) $hour, 2, '0', STR_PAD_LEFT))
                        <option value="{{ $hourValue }}" @selected($kickoffHour === $hourValue)>{{ $hourValue }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <select class="form-select" data-kickoff-minute required>
                    <option value="">Menit</option>
                    @foreach (range(0, 55, 5) as $minute)
                        @php($minuteValue = str_pad((string) $minute, 2, '0', STR_PAD_LEFT))
                        <option value="{{ $minuteValue }}" @selected($kickoffMinute === $minuteValue)>{{ $minuteValue }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-text">Format 24 jam.</div>
        @error('kickoff_time')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-8 mb-3">
        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
        <input type="text" name="venue" class="form-control" value="{{ old('venue', $matchSchedule->venue) }}" required>
    </div>
    <div class="col-lg-12 mb-3">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $matchSchedule->notes) }}</textarea>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('input[data-native-picker]').forEach((input) => {
                    const openNativePicker = () => {
                        if (typeof input.showPicker === 'function') {
                            input.showPicker();
                        }
                    };

                    input.addEventListener('click', openNativePicker);
                    input.addEventListener('focus', openNativePicker);
                });

                const formatSelect = document.querySelector('[data-competition-format]');
                const ageGroupSelect = document.querySelector('[data-age-group-select]');
                const clubSelects = document.querySelectorAll('[data-club-select]');
                const knockoutFields = document.querySelectorAll('.knockout-field');
                const clubHint = document.querySelector('[data-knockout-club-hint]');
                const boardAlert = document.querySelector('[data-knockout-board-alert]');
                const roundLabelInput = document.querySelector('[data-round-label-input]');
                const roundOrderInput = document.querySelector('[data-round-order-input]');
                const bracketSlotInput = document.querySelector('[data-bracket-slot-input]');
                const sourceMatchASelect = document.querySelector('[data-source-match-a-select]');
                const sourceMatchBSelect = document.querySelector('[data-source-match-b-select]');
                const positionCopy = document.querySelector('[data-knockout-position-copy]');
                const slotBadge = document.querySelector('[data-knockout-slot-badge]');
                const submitButton = document.querySelector('form button[type="submit"]');
                const eliminatedClubIdsByAgeGroup = @json($eliminatedClubIdsByAgeGroup);
                const knockoutSourceOptionsByAgeGroup = @json($knockoutSourceOptionsByAgeGroup);
                const currentMatchClubIds = @json($currentMatchClubIds);

                const hasKnockoutPosition = () => Boolean(roundOrderInput?.value && bracketSlotInput?.value);

                const syncKnockoutPositionSummary = () => {
                    if (!positionCopy || !slotBadge) {
                        return;
                    }

                    if (!hasKnockoutPosition()) {
                        positionCopy.textContent = 'Pilih slot dari board knockout.';
                        slotBadge.textContent = 'Posisi belum dipilih';
                        return;
                    }

                    const label = roundLabelInput?.value?.trim() || `Babak ${roundOrderInput.value}`;
                    positionCopy.textContent = `${label} • Urutan ${roundOrderInput.value} • Slot ${bracketSlotInput.value}`;
                    slotBadge.textContent = `Slot ${bracketSlotInput.value}`;
                };

                const renderSourceSelectOptions = (select, options, selectedValue, pairedSelectedValue) => {
                    if (!select) {
                        return;
                    }

                    const placeholderText = select === sourceMatchASelect
                        ? 'Tim A ditentukan manual / belum ada sumber'
                        : 'Tim B ditentukan manual / belum ada sumber';
                    const normalizedSelectedValue = String(selectedValue || '');
                    const normalizedPairedValue = String(pairedSelectedValue || '');

                    select.innerHTML = '';

                    const placeholderOption = document.createElement('option');
                    placeholderOption.value = '';
                    placeholderOption.textContent = placeholderText;
                    select.appendChild(placeholderOption);

                    let hasSelectedOption = false;

                    options.forEach((option) => {
                        const optionElement = document.createElement('option');
                        optionElement.value = String(option.id);
                        optionElement.textContent = option.label;
                        optionElement.disabled = normalizedPairedValue !== '' && normalizedPairedValue === optionElement.value;

                        if (optionElement.value === normalizedSelectedValue && !optionElement.disabled) {
                            optionElement.selected = true;
                            hasSelectedOption = true;
                        }

                        select.appendChild(optionElement);
                    });

                    if (!hasSelectedOption) {
                        select.value = '';
                    }
                };

                const syncKnockoutSourceOptions = () => {
                    if (!sourceMatchASelect || !sourceMatchBSelect || !ageGroupSelect || !roundOrderInput || !formatSelect) {
                        return;
                    }

                    const isKnockout = formatSelect.value === 'knockout';
                    const ageGroupId = ageGroupSelect.value;
                    const currentRoundOrder = Number(roundOrderInput.value || 0);
                    const availableOptions = isKnockout
                        ? (knockoutSourceOptionsByAgeGroup[String(ageGroupId)] || []).filter((option) => Number(option.round_order) < currentRoundOrder)
                        : [];

                    renderSourceSelectOptions(sourceMatchASelect, availableOptions, sourceMatchASelect.value || sourceMatchASelect.dataset.selectedValue, sourceMatchBSelect.value);
                    renderSourceSelectOptions(sourceMatchBSelect, availableOptions, sourceMatchBSelect.value || sourceMatchBSelect.dataset.selectedValue, sourceMatchASelect.value);

                    sourceMatchASelect.dataset.selectedValue = sourceMatchASelect.value;
                    sourceMatchBSelect.dataset.selectedValue = sourceMatchBSelect.value;
                    sourceMatchASelect.disabled = !isKnockout || currentRoundOrder <= 1;
                    sourceMatchBSelect.disabled = !isKnockout || currentRoundOrder <= 1;
                };

                if (formatSelect && knockoutFields.length) {
                    const syncKnockoutFields = () => {
                        const isKnockout = formatSelect.value === 'knockout';

                        knockoutFields.forEach((field) => {
                            field.classList.toggle('d-none', !isKnockout);
                        });

                        if (clubHint) {
                            clubHint.hidden = !isKnockout;
                        }

                        if (boardAlert) {
                            boardAlert.classList.toggle('d-none', !(isKnockout && !hasKnockoutPosition()));
                        }

                        if (submitButton) {
                            submitButton.disabled = isKnockout && !hasKnockoutPosition();
                        }

                        syncKnockoutPositionSummary();
                        syncKnockoutSourceOptions();
                    };

                    formatSelect.addEventListener('change', syncKnockoutFields);
                    syncKnockoutFields();
                }

                if (formatSelect && ageGroupSelect && clubSelects.length) {
                    const syncKnockoutClubOptions = () => {
                        const isKnockout = formatSelect.value === 'knockout';
                        const ageGroupId = ageGroupSelect.value;
                        const eliminatedClubIds = new Set((eliminatedClubIdsByAgeGroup[ageGroupId] || []).map((id) => Number(id)));

                        clubSelects.forEach((select) => {
                            Array.from(select.options).forEach((option) => {
                                if (!option.value) {
                                    option.disabled = false;

                                    return;
                                }

                                const clubId = Number(option.value);
                                const isCurrentClub = currentMatchClubIds.includes(clubId);
                                option.disabled = isKnockout && eliminatedClubIds.has(clubId) && !isCurrentClub;

                                if (option.disabled && option.selected) {
                                    option.selected = false;
                                }
                            });
                        });
                    };

                    formatSelect.addEventListener('change', syncKnockoutClubOptions);
                    ageGroupSelect.addEventListener('change', syncKnockoutClubOptions);
                    syncKnockoutClubOptions();
                }

                if (roundLabelInput) {
                    roundLabelInput.addEventListener('input', syncKnockoutPositionSummary);
                    syncKnockoutPositionSummary();
                }

                if (sourceMatchASelect && sourceMatchBSelect) {
                    if (ageGroupSelect) {
                        ageGroupSelect.addEventListener('change', syncKnockoutSourceOptions);
                    }

                    sourceMatchASelect.addEventListener('change', () => {
                        sourceMatchASelect.dataset.selectedValue = sourceMatchASelect.value;
                        syncKnockoutSourceOptions();
                    });

                    sourceMatchBSelect.addEventListener('change', () => {
                        sourceMatchBSelect.dataset.selectedValue = sourceMatchBSelect.value;
                        syncKnockoutSourceOptions();
                    });

                    syncKnockoutSourceOptions();
                }

                document.querySelectorAll('[data-kickoff-picker]').forEach((picker) => {
                    const hourSelect = picker.querySelector('[data-kickoff-hour]');
                    const minuteSelect = picker.querySelector('[data-kickoff-minute]');
                    const hiddenInput = picker.parentElement.querySelector('[data-kickoff-hidden]');

                    if (!hourSelect || !minuteSelect || !hiddenInput) {
                        return;
                    }

                    const syncKickoff = () => {
                        if (hourSelect.value && minuteSelect.value) {
                            hiddenInput.value = `${hourSelect.value}:${minuteSelect.value}`;
                            return;
                        }

                        hiddenInput.value = '';
                    };

                    hourSelect.addEventListener('change', syncKickoff);
                    minuteSelect.addEventListener('change', syncKickoff);
                    syncKickoff();
                });
            });
        </script>
    @endpush
@endonce
