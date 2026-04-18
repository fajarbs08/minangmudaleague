<?php

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('shows the results dropdown navigation and standalone pages', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    actingAs($admin);

    get(route('match-results.index'))
        ->assertOk()
        ->assertSeeText('Hasil Pertandingan')
        ->assertSeeText('Laporan')
        ->assertSeeText('Klasemen')
        ->assertSeeText('Top Skor')
        ->assertSeeText('Top Assist')
        ->assertSeeText('Rekap PDF')
        ->assertSee(route('reports.standings'), false)
        ->assertSee(route('reports.top-scorers'), false)
        ->assertSee(route('reports.top-assists'), false)
        ->assertSee(route('reports.overview'), false);

    get(route('reports.standings'))
        ->assertOk()
        ->assertSee('Klasemen Pertandingan');

    get(route('reports.top-scorers'))
        ->assertOk()
        ->assertSee('Top Skor');

    get(route('reports.top-assists'))
        ->assertOk()
        ->assertSee('Top Assist');

    get(route('reports.brackets'))
        ->assertOk()
        ->assertSee('Bagan Knockout');

    get(route('reports.overview'))
        ->assertOk()
        ->assertSee('Rekap Laporan Pertandingan');
});

it('builds match reports and exports them as pdf', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $ageGroup = AgeGroup::query()->firstOrCreate(
        ['code' => 'U12'],
        [
            'name' => 'U-12',
            'min_age' => 10,
            'max_age' => 12,
            'is_active' => true,
        ]
    );

    $clubA = Club::query()->create(['name' => 'Alpha FC', 'short_name' => 'ALP']);
    $clubB = Club::query()->create(['name' => 'Bravo FC', 'short_name' => 'BRV']);
    $clubC = Club::query()->create(['name' => 'Charlie FC', 'short_name' => 'CHR']);

    $strikerA = Player::query()->create([
        'club_id' => $clubA->id,
        'primary_age_group_id' => $ageGroup->id,
        'name' => 'Striker Alpha',
    ]);
    $wingerA = Player::query()->create([
        'club_id' => $clubA->id,
        'primary_age_group_id' => $ageGroup->id,
        'name' => 'Winger Alpha',
    ]);
    $forwardB = Player::query()->create([
        'club_id' => $clubB->id,
        'primary_age_group_id' => $ageGroup->id,
        'name' => 'Forward Bravo',
    ]);

    $firstMatch = MatchSchedule::query()->create([
        'age_group_id' => $ageGroup->id,
        'competition_format' => MatchSchedule::FORMAT_LEAGUE,
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubB->id,
        'match_day' => 'Pekan 1',
        'venue' => 'Lapangan Utama',
        'match_date' => now()->subDays(3)->toDateString(),
        'kickoff_time' => '08:00',
        'score_club_a' => 2,
        'score_club_b' => 1,
        'is_finished' => true,
    ]);

    $secondMatch = MatchSchedule::query()->create([
        'age_group_id' => $ageGroup->id,
        'competition_format' => MatchSchedule::FORMAT_LEAGUE,
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubC->id,
        'match_day' => 'Pekan 2',
        'venue' => 'Lapangan Utama',
        'match_date' => now()->subDays(1)->toDateString(),
        'kickoff_time' => '09:00',
        'score_club_a' => 1,
        'score_club_b' => 0,
        'is_finished' => true,
    ]);

    MatchGoal::query()->create([
        'match_id' => $firstMatch->id,
        'club_id' => $clubA->id,
        'player_id' => $strikerA->id,
        'assist_player_id' => $wingerA->id,
        'display_order' => 1,
    ]);
    MatchGoal::query()->create([
        'match_id' => $firstMatch->id,
        'club_id' => $clubA->id,
        'player_id' => $strikerA->id,
        'display_order' => 2,
    ]);
    MatchGoal::query()->create([
        'match_id' => $firstMatch->id,
        'club_id' => $clubB->id,
        'player_id' => $forwardB->id,
        'display_order' => 3,
    ]);
    MatchGoal::query()->create([
        'match_id' => $secondMatch->id,
        'club_id' => $clubA->id,
        'player_id' => $strikerA->id,
        'assist_player_id' => $wingerA->id,
        'display_order' => 1,
    ]);

    actingAs($admin);

    get(route('reports.overview', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertSee('Top Skor')
        ->assertSee('Striker Alpha')
        ->assertSee('Winger Alpha')
        ->assertSee('Klasemen Liga')
        ->assertSee('ALP');

    get(route('reports.top-scorers', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertSee('Striker Alpha');

    get(route('reports.top-assists', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertSee('Winger Alpha');

    get(route('reports.standings.pdf', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    get(route('reports.top-scorers.pdf', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    get(route('reports.top-assists.pdf', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    get(route('reports.overview.pdf', ['age_group_id' => $ageGroup->id]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
