@php
    $sectionTitle = $sectionTitle ?? 'Klasemen Liga';
    $sectionDescription = $sectionDescription ?? 'Disusun dari hasil pertandingan liga yang telah selesai.';
    $emptyMessage = $emptyMessage ?? 'Belum ada klasemen yang bisa ditampilkan.';
@endphp

<div class="row g-4">
    @forelse ($standings as $standing)
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-1">{{ $sectionTitle }} {{ $standing['age_group']?->name ?: '-' }}</h4>
                    <p class="text-muted mb-0">{{ $sectionDescription }}</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive competition-table-wrap">
                        <table class="table competition-table align-middle text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Klub</th>
                                    <th>Main</th>
                                    <th>Menang</th>
                                    <th>Imbang</th>
                                    <th>Kalah</th>
                                    <th>GM</th>
                                    <th>GK</th>
                                    <th>SG</th>
                                    <th>Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($standing['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['position'] }}</td>
                                        <td class="fw-semibold">{{ $row['club_name'] }}</td>
                                        <td>{{ $row['played'] }}</td>
                                        <td>{{ $row['won'] }}</td>
                                        <td>{{ $row['drawn'] }}</td>
                                        <td>{{ $row['lost'] }}</td>
                                        <td>{{ $row['goals_for'] }}</td>
                                        <td>{{ $row['goals_against'] }}</td>
                                        <td>{{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}</td>
                                        <td><span class="fw-bold">{{ $row['points'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body py-5 text-center text-muted">{{ $emptyMessage }}</div>
            </div>
        </div>
    @endforelse
</div>
