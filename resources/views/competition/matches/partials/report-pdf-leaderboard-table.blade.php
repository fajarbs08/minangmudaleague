@forelse ($leaderboards as $leaderboard)
    <div class="report-block" style="page-break-inside: avoid;">
        <div class="group-banner">{{ strtoupper($leaderboard['age_group']?->name ?: '-') }}</div>
        <table class="report-frame report-data">
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">Pos</th>
                    <th>Pemain</th>
                    <th>Klub</th>
                    <th class="text-right" style="width: 36px;">{{ $metricLabel }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaderboard['rows'] as $row)
                    <tr class="report-row-{{ $loop->odd ? 'odd' : 'even' }}">
                        <td class="text-center cell-tight">{{ $row['position'] }}</td>
                        <td>{{ $row['player_name'] }}</td>
                        <td class="cell-club">{{ $row['club_name'] }}</td>
                        <td class="text-right points cell-tight">{{ $row['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="report-note">
            <strong>Catatan:</strong> Peringkat diurutkan dari total {{ strtolower($metricLabel) }} terbanyak.
            Jika jumlah sama, urutan pemain mengikuti nama secara alfabetis.
        </div>
    </div>
@empty
    <div class="report-block">
        <table class="report-frame report-data">
            <tbody>
                <tr class="empty-row">
                    <td>{{ $emptyMessage }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforelse
