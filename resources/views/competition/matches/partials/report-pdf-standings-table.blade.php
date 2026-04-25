@forelse ($standings as $standing)
    <div class="report-block" style="page-break-inside: avoid;">
        <div class="group-banner">{{ strtoupper($standing['age_group']?->name ?: '-') }}</div>
        <table class="report-frame report-data compact-stats">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8mm;">Pos</th>
                    <th style="width: 96mm;">Klub</th>
                    <th class="text-center" style="width: 12mm;">Main</th>
                    <th class="text-center" style="width: 9mm;">M</th>
                    <th class="text-center" style="width: 9mm;">S</th>
                    <th class="text-center" style="width: 9mm;">K</th>
                    <th class="text-center" style="width: 10mm;">GM</th>
                    <th class="text-center" style="width: 10mm;">GK</th>
                    <th class="text-center" style="width: 14mm;">SG</th>
                    <th class="text-center" style="width: 15mm;">Poin</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($standing['rows'] as $row)
                    <tr class="report-row-{{ $loop->odd ? 'odd' : 'even' }}">
                        <td class="text-center cell-tight">{{ $row['position'] }}</td>
                        <td class="cell-club">{{ $row['club_name'] }}</td>
                        <td class="text-center cell-tight">{{ $row['played'] }}</td>
                        <td class="text-center cell-tight">{{ $row['won'] }}</td>
                        <td class="text-center cell-tight">{{ $row['drawn'] }}</td>
                        <td class="text-center cell-tight">{{ $row['lost'] }}</td>
                        <td class="text-center cell-tight">{{ $row['goals_for'] }}</td>
                        <td class="text-center cell-tight">{{ $row['goals_against'] }}</td>
                        <td class="text-center cell-tight">{{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}</td>
                        <td class="text-center points cell-tight">{{ $row['points'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="report-note">
            <strong>Catatan:</strong> M = menang, S = seri, K = kalah, GM = gol memasukkan, GK = gol kebobolan, SG = selisih gol.
            Poin dihitung dengan format 3 untuk menang, 1 untuk seri, dan 0 untuk kalah.
        </div>
    </div>
@empty
    <div class="report-block">
        <table class="report-frame report-data">
            <tbody>
                <tr class="empty-row">
                    <td>Belum ada klasemen liga untuk filter ini.</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforelse
