<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\MatchSchedule;

class KnockoutPlayin16Seeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $this->call([
            AgeGroupSeeder::class,
            ClubSeeder::class,
        ]);

        $ageGroup = AgeGroup::query()->where('code', 'U12')->firstOrFail();

        $clubs = $this->resolveSixteenClubs();

        MatchSchedule::query()->forActiveSeason()
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->where('age_group_id', $ageGroup->id)
            ->delete();

        $baseDate = now()->addDays(15)->startOfDay();

        $playIn1 = $this->createKnockoutMatch($ageGroup->id, 1, 1, 'Babak Play-in', 'Play-in 1', $clubs[0], $clubs[15], $baseDate->copy(), '09:00', 2, 0);
        $playIn2 = $this->createKnockoutMatch($ageGroup->id, 1, 2, 'Babak Play-in', 'Play-in 2', $clubs[1], $clubs[14], $baseDate->copy(), '10:30', 1, 0);
        $playIn3 = $this->createKnockoutMatch($ageGroup->id, 1, 3, 'Babak Play-in', 'Play-in 3', $clubs[2], $clubs[13], $baseDate->copy(), '12:00', 3, 1);
        $playIn4 = $this->createKnockoutMatch($ageGroup->id, 1, 4, 'Babak Play-in', 'Play-in 4', $clubs[3], $clubs[12], $baseDate->copy(), '13:30', 2, 1);
        $playIn5 = $this->createKnockoutMatch($ageGroup->id, 1, 5, 'Babak Play-in', 'Play-in 5', $clubs[4], $clubs[11], $baseDate->copy(), '15:00', 1, 0);
        $playIn6 = $this->createKnockoutMatch($ageGroup->id, 1, 6, 'Babak Play-in', 'Play-in 6', $clubs[5], $clubs[10], $baseDate->copy(), '16:30', 2, 0);
        $playIn7 = $this->createKnockoutMatch($ageGroup->id, 1, 7, 'Babak Play-in', 'Play-in 7', $clubs[6], $clubs[9], $baseDate->copy(), '18:00', 3, 2);
        $playIn8 = $this->createKnockoutMatch($ageGroup->id, 1, 8, 'Babak Play-in', 'Play-in 8', $clubs[7], $clubs[8], $baseDate->copy(), '19:30', 2, 1);

        $qf1 = $this->createKnockoutMatch($ageGroup->id, 2, 1, 'Perempat Final', 'Perempat Final 1', $clubs[0], $clubs[1], $baseDate->copy()->addDays(2), '14:00', 2, 1, $playIn1->id, $playIn2->id);
        $qf2 = $this->createKnockoutMatch($ageGroup->id, 2, 2, 'Perempat Final', 'Perempat Final 2', $clubs[2], $clubs[3], $baseDate->copy()->addDays(2), '16:00', 1, 0, $playIn3->id, $playIn4->id);
        $qf3 = $this->createKnockoutMatch($ageGroup->id, 2, 3, 'Perempat Final', 'Perempat Final 3', $clubs[4], $clubs[5], $baseDate->copy()->addDays(3), '14:00', 0, 2, $playIn5->id, $playIn6->id);
        $qf4 = $this->createKnockoutMatch($ageGroup->id, 2, 4, 'Perempat Final', 'Perempat Final 4', $clubs[6], $clubs[7], $baseDate->copy()->addDays(3), '16:00', 3, 1, $playIn7->id, $playIn8->id);

        $sf1 = $this->createKnockoutMatch($ageGroup->id, 3, 1, 'Semifinal', 'Semifinal 1', $clubs[0], $clubs[2], $baseDate->copy()->addDays(5), '15:00', 1, 0, $qf1->id, $qf2->id);
        $sf2 = $this->createKnockoutMatch($ageGroup->id, 3, 2, 'Semifinal', 'Semifinal 2', $clubs[5], $clubs[6], $baseDate->copy()->addDays(5), '17:00', 0, 2, $qf3->id, $qf4->id);

        $this->createKnockoutMatch($ageGroup->id, 4, 1, 'Final', 'Final', $clubs[0], $clubs[6], $baseDate->copy()->addDays(7), '16:00', 2, 1, $sf1->id, $sf2->id);
    }

    /**
     * @return array<int, Club>
     */
    private function resolveSixteenClubs(): array
    {
        $clubs = Club::query()->orderBy('id')->get();

        if ($clubs->count() < 16) {
            $needed = 16 - $clubs->count();

            for ($index = 1; $index <= $needed; $index++) {
                $number = $clubs->count() + $index;
                $club = $this->upsertClub(
                    ['name' => 'Klub Demo '.$number],
                    [
                        'short_name' => 'KD'.$number,
                        'manager_name' => 'Manajer Demo '.$number,
                        'zone' => 'Zona Demo',
                        'verification_status' => Club::STATUS_APPROVED,
                    ]
                );

                $clubs->push($club);
            }
        }

        return $clubs->take(16)->values()->all();
    }

    private function createKnockoutMatch(
        int $ageGroupId,
        int $roundOrder,
        int $slot,
        string $roundLabel,
        string $matchDay,
        Club $clubA,
        Club $clubB,
        $matchDate,
        string $kickoff,
        int $scoreA,
        int $scoreB,
        ?int $sourceAId = null,
        ?int $sourceBId = null
    ): MatchSchedule {
        $seasonId = $this->activeSeasonId();

        return MatchSchedule::query()->forActiveSeason()->create([
            'season_id' => $seasonId,
            'age_group_id' => $ageGroupId,
            'competition_format' => MatchSchedule::FORMAT_KNOCKOUT,
            'round_label' => $roundLabel,
            'round_order' => $roundOrder,
            'bracket_slot' => $slot,
            'source_match_a_id' => $sourceAId,
            'source_match_b_id' => $sourceBId,
            'club_a_id' => $clubA->id,
            'club_a_season_id' => $this->seasonSnapshots()->seasonClubIdForClub($clubA->id, $seasonId),
            'club_b_id' => $clubB->id,
            'club_b_season_id' => $this->seasonSnapshots()->seasonClubIdForClub($clubB->id, $seasonId),
            'match_day' => $matchDay,
            'venue' => 'Stadion Liga Anak Piaman Laweh',
            'match_date' => $matchDate->toDateString(),
            'kickoff_time' => $kickoff,
            'score_club_a' => $scoreA,
            'score_club_b' => $scoreB,
            'is_finished' => true,
        ]);
    }
}
