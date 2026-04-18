<form method="GET" action="{{ route('public.results') }}" class="tw-mt-6" data-results-filter-form>
    <div class="tw-grid tw-gap-3 lg:tw-grid-cols-[minmax(0,1fr)_220px_180px_180px]">
        <div>
            <label for="result-keyword" class="tw-mb-2 tw-block tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white/50">Search Klub</label>
            <input id="result-keyword" type="search" name="q" value="{{ $selectedKeyword }}" placeholder="Cari nama klub atau singkatan" data-results-auto-search class="tw-h-14 tw-w-full tw-border tw-border-white/10 tw-bg-white/5 tw-px-4 tw-text-sm tw-text-white tw-outline-none placeholder:tw-text-white/38 focus:tw-border-lap-red">
        </div>
        <div>
            <label for="result-age-group" class="tw-mb-2 tw-block tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white/50">Kategori Usia</label>
            <select id="result-age-group" name="age_group_id" data-results-auto-submit class="tw-h-14 tw-w-full tw-border tw-border-white/10 tw-bg-white/5 tw-px-4 tw-text-sm tw-text-white tw-outline-none focus:tw-border-lap-red">
                <option value="">Semua kategori</option>
                @foreach ($resultAgeGroups as $ageGroup)
                    <option value="{{ $ageGroup->id }}" @selected((string) $selectedAgeGroupId === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="result-date-from" class="tw-mb-2 tw-block tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white/50">Dari Tanggal</label>
            <input id="result-date-from" type="date" name="date_from" value="{{ $selectedDateFrom }}" data-results-auto-submit class="tw-h-14 tw-w-full tw-border tw-border-white/10 tw-bg-white/5 tw-px-4 tw-text-sm tw-text-white tw-outline-none focus:tw-border-lap-red">
        </div>
        <div>
            <label for="result-date-to" class="tw-mb-2 tw-block tw-text-[0.72rem] tw-font-black tw-uppercase tw-tracking-[0.22em] tw-text-white/50">Sampai Tanggal</label>
            <input id="result-date-to" type="date" name="date_to" value="{{ $selectedDateTo }}" data-results-auto-submit class="tw-h-14 tw-w-full tw-border tw-border-white/10 tw-bg-white/5 tw-px-4 tw-text-sm tw-text-white tw-outline-none focus:tw-border-lap-red">
        </div>
    </div>
</form>
