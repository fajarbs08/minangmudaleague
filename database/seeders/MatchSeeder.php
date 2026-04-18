<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\Player;
use App\Models\User;

class MatchSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $admin = $this->adminUser();

        MatchGoal::query()->delete();

        foreach ($this->matchPlans() as $plan) {
            $this->seedMatchPlan($plan, $admin);
        }
    }

    private function seedMatchPlan(array $plan, $admin): void
    {
        $ageGroup = $this->getAgeGroup($plan['age_group']);
        $homeClub = $this->getClub($plan['home_club']);
        $awayClub = $this->getClub($plan['away_club']);

        $match = $this->upsertMatchSchedule(
            [
                'age_group_id' => $ageGroup->id,
                'club_a_id' => $homeClub->id,
                'club_b_id' => $awayClub->id,
                'match_day' => $plan['match_day'],
            ],
            [
                'competition_format' => MatchSchedule::FORMAT_LEAGUE,
                'round_label' => null,
                'round_order' => null,
                'bracket_slot' => null,
                'venue' => $plan['venue'],
                'match_date' => $plan['match_date'],
                'kickoff_time' => $plan['kickoff_time'],
                'score_club_a' => $plan['score_home'],
                'score_club_b' => $plan['score_away'],
                'is_finished' => true,
            ]
        );

        $this->seedLineup(
            match: $match,
            club: $homeClub,
            ageGroup: $ageGroup,
            coachName: $this->coachNameFor($homeClub, $ageGroup),
            jerseyColor: $plan['home_color'],
            goalkeeperColor: $plan['home_gk_color'],
            venue: $plan['venue'],
            kickoffTime: $plan['kickoff_time'],
            admin: $admin,
            titleSuffix: 'Home'
        );

        $this->seedLineup(
            match: $match,
            club: $awayClub,
            ageGroup: $ageGroup,
            coachName: $this->coachNameFor($awayClub, $ageGroup),
            jerseyColor: $plan['away_color'],
            goalkeeperColor: $plan['away_gk_color'],
            venue: $plan['venue'],
            kickoffTime: $plan['kickoff_time'],
            admin: $admin,
            titleSuffix: 'Away'
        );

        foreach ($plan['goals'] as $order => $goal) {
            $club = $goal['side'] === 'home' ? $homeClub : $awayClub;
            $players = $this->rosterPlayers($club, $ageGroup);
            $scorer = $players->firstWhere('jersey_number', $goal['scorer']);
            $assist = filled($goal['assist'] ?? null)
                ? $players->firstWhere('jersey_number', $goal['assist'])
                : null;

            if (! $scorer) {
                continue;
            }

            MatchGoal::create([
                'match_id' => $match->id,
                'club_id' => $club->id,
                'player_id' => $scorer->id,
                'assist_player_id' => $assist?->id,
                'display_order' => $order + 1,
            ]);
        }
    }

    private function seedLineup(
        MatchSchedule $match,
        Club $club,
        AgeGroup $ageGroup,
        string $coachName,
        string $jerseyColor,
        string $goalkeeperColor,
        string $venue,
        string $kickoffTime,
        User $admin,
        string $titleSuffix
    ): void {
        $players = $this->rosterPlayers($club, $ageGroup);

        $lineup = $this->upsertLineup($club, sprintf('DSP %s %s %s', $club->short_name, $ageGroup->code, $titleSuffix), [
            'match_id' => $match->id,
            'age_group_id' => $ageGroup->id,
            'match_day' => $match->match_day,
            'match_date' => $match->match_date?->toDateString() ?: $match->match_date,
            'played_time' => $kickoffTime,
            'coach_name' => $coachName,
            'jersey_color' => $jerseyColor,
            'goalkeeper_jersey_color' => $goalkeeperColor,
            'played_at' => $venue,
            'notes' => 'DSP demo untuk '.$club->short_name.' '.$ageGroup->name,
            'verification_status' => LineupList::STATUS_APPROVED,
            'verification_notes' => 'DSP demo sudah diverifikasi.',
            'submitted_at' => now()->subDays(2),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDay(),
        ]);

        $this->syncLineupPlayers($lineup, array_merge(
            $players->take(LineupList::REQUIRED_STARTERS)->map(fn ($player) => [
                'player' => $player,
                'role' => LineupList::ROLE_STARTER,
            ])->all(),
            $players->slice(LineupList::REQUIRED_STARTERS, 2)->map(fn ($player) => [
                'player' => $player,
                'role' => LineupList::ROLE_SUBSTITUTE,
            ])->all()
        ));
    }

    private function rosterPlayers(Club $club, AgeGroup $ageGroup)
    {
        return Player::query()
            ->where('club_id', $club->id)
            ->where('primary_age_group_id', $ageGroup->id)
            ->where('verification_status', Player::STATUS_APPROVED)
            ->orderBy('jersey_number')
            ->get();
    }

    private function coachNameFor(Club $club, AgeGroup $ageGroup): string
    {
        return Official::query()
            ->where('club_id', $club->id)
            ->where('age_group_id', $ageGroup->id)
            ->orderBy('id')
            ->value('name') ?: $club->manager_name;
    }

    private function matchPlans(): array
    {
        return [
            [
                'age_group' => 'U12',
                'home_club' => 'Garuda Muda FC',
                'away_club' => 'Elang Nusantara',
                'match_day' => 'Matchday 1',
                'venue' => 'Stadion Garuda',
                'match_date' => now()->addDays(5)->toDateString(),
                'kickoff_time' => '15:00',
                'score_home' => 2,
                'score_away' => 1,
                'home_color' => 'Merah',
                'away_color' => 'Putih',
                'home_gk_color' => 'Hitam',
                'away_gk_color' => 'Kuning',
                'goals' => [
                    ['side' => 'home', 'scorer' => 9, 'assist' => 7],
                    ['side' => 'home', 'scorer' => 10, 'assist' => 8],
                    ['side' => 'away', 'scorer' => 11, 'assist' => 6],
                ],
            ],
            [
                'age_group' => 'U14',
                'home_club' => 'Elang Nusantara',
                'away_club' => 'Rajawali City',
                'match_day' => 'Matchday 2',
                'venue' => 'Lapangan Nusantara',
                'match_date' => now()->addDays(7)->toDateString(),
                'kickoff_time' => '13:00',
                'score_home' => 1,
                'score_away' => 1,
                'home_color' => 'Biru',
                'away_color' => 'Hijau',
                'home_gk_color' => 'Putih',
                'away_gk_color' => 'Hitam',
                'goals' => [
                    ['side' => 'home', 'scorer' => 9, 'assist' => 7],
                    ['side' => 'away', 'scorer' => 10, 'assist' => 8],
                ],
            ],
            [
                'age_group' => 'U16',
                'home_club' => 'Rajawali City',
                'away_club' => 'Garuda Muda FC',
                'match_day' => 'Matchday 3',
                'venue' => 'Rajawali Arena',
                'match_date' => now()->addDays(9)->toDateString(),
                'kickoff_time' => '16:00',
                'score_home' => 3,
                'score_away' => 1,
                'home_color' => 'Navy',
                'away_color' => 'Merah',
                'home_gk_color' => 'Kuning',
                'away_gk_color' => 'Hitam',
                'goals' => [
                    ['side' => 'home', 'scorer' => 9, 'assist' => 7],
                    ['side' => 'home', 'scorer' => 10, 'assist' => 8],
                    ['side' => 'home', 'scorer' => 11, 'assist' => 6],
                    ['side' => 'away', 'scorer' => 9, 'assist' => 7],
                ],
            ],
        ];
    }
}
