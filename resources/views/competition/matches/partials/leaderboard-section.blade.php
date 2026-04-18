<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="card-title mb-1">{{ $title }}</h4>
            <p class="text-muted mb-0">{{ $description }}</p>
        </div>
        <span class="badge bg-light text-dark border">{{ $leaderboards->sum(fn ($group) => collect($group['rows'])->count()) }} pemain</span>
    </div>
    <div class="card-body p-0">
        @forelse ($leaderboards as $leaderboard)
            <div class="p-3 {{ $loop->first ? '' : 'border-top' }}">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="mb-0">{{ $leaderboard['age_group']?->name ?: '-' }}</h5>
                    <span class="badge bg-secondary-subtle text-secondary">{{ collect($leaderboard['rows'])->count() }} pemain</span>
                </div>
                <div class="table-responsive competition-table-wrap">
                    <table class="table competition-table align-middle text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Pemain</th>
                                <th>Klub</th>
                                <th>{{ $metricLabel }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaderboard['rows'] as $row)
                                <tr>
                                    <td>{{ $row['position'] }}</td>
                                    <td class="fw-semibold">{{ $row['player_name'] }}</td>
                                    <td>{{ $row['club_name'] }}</td>
                                    <td><span class="fw-bold">{{ $row['total'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="py-5 px-3 text-center text-muted">{{ $emptyMessage }}</div>
        @endforelse
    </div>
</div>
