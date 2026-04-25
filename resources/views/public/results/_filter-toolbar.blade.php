<details aria-labelledby="result-filter-toolbar-heading" class="lap-summary-card lap-results-filter-shell mb--40" @if ($activeFilterCount > 0) open @endif>
    <summary class="lap-results-filter-toggle">
        <div class="lap-results-filter-toggle-copy">
            <p class="lap-results-filter-kicker">Pencarian Cepat</p>
            <h2 id="result-filter-toolbar-heading" class="lap-results-panel-title">Cari pertandingan tertentu bila perlu</h2>
            <p class="lap-copy mb-0">Filter ini untuk membantu mencari klub, kategori umur, format, status, atau tanggal tertentu. Daftar hasil di bawah tetap menjadi fokus utama halaman.</p>
        </div>
        <span class="lap-results-filter-toggle-action">{{ $activeFilterCount > 0 ? number_format($activeFilterCount).' filter aktif' : 'Buka filter' }}</span>
    </summary>

    <div class="lap-results-filter-body">
        <div class="lap-results-filter-meta">
            <span class="item"><strong>{{ number_format($activeArchiveCount) }}</strong> hasil siap ditampilkan</span>
            @isset($statusContextLabel)
                <span class="item">{{ $statusContextLabel }}</span>
            @endisset
        </div>

        <form method="GET" action="{{ $actionUrl }}">
            <div class="row g-4 align-items-end">
            <div class="col-xl-4 col-md-6">
                <label for="result-keyword" class="form-label text-uppercase fw-bold small">Cari klub</label>
                <input id="result-keyword" type="search" name="q" value="{{ $selectedClub }}" placeholder="Cari nama klub" class="form-control">
            </div>

            <div class="col-xl-2 col-md-6">
                <label for="result-age-group" class="form-label text-uppercase fw-bold small">Kategori umur</label>
                <select id="result-age-group" name="age_group_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($ageGroups as $ageGroup)
                        @php
                            $ageGroupId = data_get($ageGroup, 'id');
                            $ageGroupName = data_get($ageGroup, 'name', $ageGroupId);
                        @endphp
                        <option value="{{ $ageGroupId }}" @selected((string) $selectedAgeGroupId === (string) $ageGroupId)>{{ $ageGroupName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-2 col-md-6">
                <label for="result-competition-format" class="form-label text-uppercase fw-bold small">Format</label>
                <select id="result-competition-format" name="competition_format" class="form-select">
                    <option value="">Semua format</option>
                    @foreach ($formatOptions as $formatValue => $formatLabel)
                        <option value="{{ $formatValue }}" @selected((string) $selectedCompetitionFormat === (string) $formatValue)>{{ $formatLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-2 col-md-6">
                <label for="result-status" class="form-label text-uppercase fw-bold small">Status</label>
                <select id="result-status" name="status" class="form-select">
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option['value'] }}" @selected((string) $selectedStatus === (string) $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-2 col-md-6">
                <label for="result-date-from" class="form-label text-uppercase fw-bold small">Dari tanggal</label>
                <input id="result-date-from" type="date" name="date_from" value="{{ $selectedDateFrom }}" class="form-control">
            </div>

            <div class="col-xl-2 col-md-6">
                <label for="result-date-to" class="form-label text-uppercase fw-bold small">Sampai tanggal</label>
                <input id="result-date-to" type="date" name="date_to" value="{{ $selectedDateTo }}" class="form-control">
            </div>

            <div class="col-xl-2 col-md-6">
                <button type="submit" class="btn btn-primary w-100">Terapkan</button>
            </div>

            <div class="col-xl-2 col-md-6">
                <a href="{{ $resetUrl }}" class="btn btn-light w-100">Reset</a>
            </div>
            </div>

            @if (! empty($activeFilterLabels))
                <div class="lap-results-filter-tags">
                    @foreach ($activeFilterLabels as $chip)
                        <span>{{ $chip }}</span>
                    @endforeach
                </div>
            @endif
        </form>
    </div>
</details>
