<div class="text-muted small mb-3"><span class="text-danger">*</span> wajib diisi.</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <label class="form-label">Kelompok Usia <span class="text-danger">*</span></label>
        <select name="age_group_id" class="form-select" required>
            <option value="">Pilih kelompok usia</option>
            @foreach ($ageGroups as $ageGroup)
                <option value="{{ $ageGroup->id }}" @selected((string) old('age_group_id', $matchSchedule->age_group_id) === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Klub A <span class="text-danger">*</span></label>
        <select name="club_a_id" class="form-select" required>
            <option value="">Pilih klub</option>
            @foreach ($clubs as $club)
                <option value="{{ $club->id }}" @selected((string) old('club_a_id', $matchSchedule->club_a_id) === (string) $club->id)>{{ $club->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Klub B <span class="text-danger">*</span></label>
        <select name="club_b_id" class="form-select" required>
            <option value="">Pilih klub</option>
            @foreach ($clubs as $club)
                <option value="{{ $club->id }}" @selected((string) old('club_b_id', $matchSchedule->club_b_id) === (string) $club->id)>{{ $club->name }}</option>
            @endforeach
        </select>
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
        <input
            type="text"
            name="kickoff_time"
            class="form-control"
            value="{{ old('kickoff_time', optional($matchSchedule->kickoff_time)->format('H:i')) }}"
            data-time-picker-24h
            placeholder="HH:MM"
            inputmode="numeric"
            autocomplete="off"
            required
        >
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
                    input.addEventListener('click', () => {
                        if (typeof input.showPicker === 'function') {
                            input.showPicker();
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
