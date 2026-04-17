@php
    $eliminatedClubIdsByAgeGroup = $knockoutEliminatedClubIdsByAgeGroup ?? [];
    $currentMatchClubIds = collect($currentMatchClubIds ?? [])->map(fn ($id) => (int) $id)->values()->all();
    $kickoffValue = old('kickoff_time', optional($matchSchedule->kickoff_time)->format('H:i'));
    [$kickoffHour, $kickoffMinute] = str_contains((string) $kickoffValue, ':')
        ? explode(':', $kickoffValue, 2)
        : [null, null];
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
        <div class="form-text">`Liga` untuk klasemen, `Knockout` untuk bracket.</div>
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
    <div class="col-lg-4 mb-3 knockout-field">
        <label class="form-label">Label Babak</label>
        <input
            type="text"
            name="round_label"
            class="form-control"
            value="{{ old('round_label', $matchSchedule->round_label) }}"
            placeholder="Contoh: Perempat Final"
        >
        <div class="form-text">Contoh: Perempat Final, Semifinal, Final.</div>
    </div>
    <div class="col-lg-4 mb-3 knockout-field">
        <label class="form-label">Posisi Kolom Bracket</label>
        <input
            type="number"
            name="round_order"
            min="1"
            class="form-control"
            value="{{ old('round_order', $matchSchedule->round_order) }}"
            placeholder="1"
        >
        <div class="form-text">Menentukan urutan kolom babak. Contoh: 1 Perempat Final, 2 Semifinal, 3 Final.</div>
    </div>
    <div class="col-lg-4 mb-3 knockout-field">
        <label class="form-label">Posisi Match di Babak</label>
        <input
            type="number"
            name="bracket_slot"
            min="1"
            class="form-control"
            value="{{ old('bracket_slot', $matchSchedule->bracket_slot) }}"
            placeholder="1"
        >
        <div class="form-text">Menentukan urutan match dari atas ke bawah dalam babak yang sama.</div>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Matchday <span class="text-danger">*</span></label>
        <input type="text" name="match_day" class="form-control" value="{{ old('match_day', $matchSchedule->match_day) }}" required>
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
        <label class="form-label">Jam Kick-off <span class="text-danger">*</span></label>
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
        <label class="form-label">Venue <span class="text-danger">*</span></label>
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
                const eliminatedClubIdsByAgeGroup = @json($eliminatedClubIdsByAgeGroup);
                const currentMatchClubIds = @json($currentMatchClubIds);

                if (formatSelect && knockoutFields.length) {
                    const syncKnockoutFields = () => {
                        const isKnockout = formatSelect.value === 'knockout';

                        knockoutFields.forEach((field) => {
                            field.classList.toggle('d-none', !isKnockout);
                        });

                        if (clubHint) {
                            clubHint.hidden = !isKnockout;
                        }
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
