<?php

namespace Tests\Feature;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Player;
use App\Models\PlayerAgeGroup;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LineupListRosterRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_u12_lineup_accepts_exactly_eight_starters(): void
    {
        $scenario = $this->createLineupScenario('U12', 10);
        $starters = $scenario['players']->take(8);

        $response = $this->actingAs($scenario['user'])
            ->post(route('lineup-lists.store'), $this->lineupPayload($scenario, $starters));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $lineup = LineupList::query()->with('players')->firstOrFail();

        $this->assertSame(8, $lineup->starter_count);
        $this->assertSame('Lapangan U12 Custom', $lineup->played_at);
        $this->assertSame('07:45', optional($lineup->played_time)->format('H:i'));
    }

    public function test_non_u12_lineup_still_requires_eleven_starters(): void
    {
        $scenario = $this->createLineupScenario('U14', 11);
        $starters = $scenario['players']->take(8);

        $response = $this->actingAs($scenario['user'])
            ->from(route('lineup-lists.create'))
            ->post(route('lineup-lists.store'), $this->lineupPayload($scenario, $starters));

        $response->assertRedirect(route('lineup-lists.create'));
        $response->assertSessionHasErrors('starter_player_ids');

        $this->assertDatabaseCount('lineup_lists', 0);
    }

    private function createLineupScenario(string $ageCode, int $playerCount): array
    {
        $season = Season::query()->active()->firstOrFail();
        $ageGroup = AgeGroup::query()->where('code', $ageCode)->firstOrFail();
        $user = User::factory()->create([
            'role' => 'club',
            'is_active' => true,
        ]);
        $club = $this->createApprovedClub($user, "Klub {$ageCode} Alpha", "K{$ageCode}A");
        $opponent = $this->createApprovedClub(null, "Klub {$ageCode} Bravo", "K{$ageCode}B");

        $match = MatchSchedule::query()->create([
            'season_id' => $season->id,
            'age_group_id' => $ageGroup->id,
            'club_a_id' => $club->id,
            'club_b_id' => $opponent->id,
            'match_day' => "Matchday {$ageCode}",
            'venue' => "Lapangan {$ageCode}",
            'match_date' => Carbon::parse('2026-04-12'),
            'kickoff_time' => '09:20',
        ]);

        $players = collect(range(1, $playerCount))->map(function (int $number) use ($club, $ageGroup, $season) {
            $player = Player::query()->create([
                'club_id' => $club->id,
                'primary_age_group_id' => $ageGroup->id,
                'name' => "Pemain {$ageGroup->code} {$number}",
                'jersey_number' => $number,
                'position' => $number === 1 ? 'GK' : 'MID',
                'verification_status' => Player::STATUS_APPROVED,
            ]);

            PlayerAgeGroup::query()->create([
                'player_id' => $player->id,
                'age_group_id' => $ageGroup->id,
                'season_id' => $season->id,
                'season' => $season->name,
                'jersey_number' => $number,
                'position' => $number === 1 ? 'GK' : 'MID',
                'registration_status' => Player::STATUS_APPROVED,
                'status_date' => now(),
            ]);

            return $player;
        });

        return compact('ageGroup', 'club', 'match', 'players', 'season', 'user');
    }

    private function createApprovedClub(?User $user, string $name, string $shortName): Club
    {
        return Club::query()->create([
            'user_id' => $user?->id,
            'name' => $name,
            'short_name' => $shortName,
            'verification_status' => Club::STATUS_APPROVED,
        ]);
    }

    private function lineupPayload(array $scenario, $starters): array
    {
        $starterIds = $starters->pluck('id')->all();
        $jerseys = $starters->mapWithKeys(fn (Player $player) => [$player->id => (string) $player->jersey_number])->all();

        return [
            'match_id' => $scenario['match']->id,
            'club_id' => $scenario['club']->id,
            'title' => "DSP {$scenario['ageGroup']->code}",
            'played_at' => "Lapangan {$scenario['ageGroup']->code} Custom",
            'match_date' => '2026-04-12',
            'played_time' => '07:45',
            'jersey_color' => 'Hijau',
            'goalkeeper_jersey_color' => 'Hitam-hitam',
            'starter_player_ids' => $starterIds,
            'starter_jerseys' => $jerseys,
        ];
    }
}
