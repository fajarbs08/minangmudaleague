<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchGoal;
use App\Models\MatchSchedule;

class KnockoutMultiAgeSeeder extends AbstractDemoSeeder
{
    public function run(): void
    {
        $this->call([
            AgeGroupSeeder::class,
            ClubSeeder::class,
        ]);

        $ageGroups = AgeGroup::competition()->get()->keyBy('code');

        $this->purgeExistingKnockoutMatches($ageGroups->pluck('id')->all());

        foreach ($this->bracketPlans() as $ageGroupCode => $plan) {
            $ageGroup = $ageGroups->get($ageGroupCode);

            if (! $ageGroup) {
                continue;
            }

            $this->seedBracketForAgeGroup($ageGroup, $plan);
        }
    }

    private function purgeExistingKnockoutMatches(array $ageGroupIds): void
    {
        $matchIds = MatchSchedule::query()
            ->where('competition_format', MatchSchedule::FORMAT_KNOCKOUT)
            ->whereIn('age_group_id', $ageGroupIds)
            ->pluck('id');

        if ($matchIds->isEmpty()) {
            return;
        }

        MatchGoal::query()->whereIn('match_id', $matchIds)->delete();
        LineupList::query()->whereIn('match_id', $matchIds)->delete();
        MatchSchedule::query()->whereIn('id', $matchIds)->delete();
    }

    private function seedBracketForAgeGroup(AgeGroup $ageGroup, array $plan): void
    {
        $clubs = $this->resolveClubs($plan['clubs']);

        if (count($clubs) !== 8) {
            throw new \LogicException('Seeder knockout multi usia membutuhkan tepat 8 klub per kelompok usia.');
        }

        $baseDate = now()->startOfDay()->addDays($plan['start_offset_days']);

        $quarterfinals = [
            $this->createFinishedKnockoutMatch(
                ageGroupId: $ageGroup->id,
                roundOrder: 1,
                slot: 1,
                roundLabel: 'Perempat Final',
                matchDay: $ageGroup->code.' Perempat Final 1',
                clubA: $clubs[0],
                clubB: $clubs[7],
                matchDate: $baseDate->copy(),
                kickoff: '09:00',
                venue: $plan['venue'],
                score: $plan['quarterfinal_scores'][0],
            ),
            $this->createFinishedKnockoutMatch(
                ageGroupId: $ageGroup->id,
                roundOrder: 1,
                slot: 2,
                roundLabel: 'Perempat Final',
                matchDay: $ageGroup->code.' Perempat Final 2',
                clubA: $clubs[3],
                clubB: $clubs[4],
                matchDate: $baseDate->copy(),
                kickoff: '11:00',
                venue: $plan['venue'],
                score: $plan['quarterfinal_scores'][1],
            ),
            $this->createFinishedKnockoutMatch(
                ageGroupId: $ageGroup->id,
                roundOrder: 1,
                slot: 3,
                roundLabel: 'Perempat Final',
                matchDay: $ageGroup->code.' Perempat Final 3',
                clubA: $clubs[1],
                clubB: $clubs[6],
                matchDate: $baseDate->copy()->addDay(),
                kickoff: '09:00',
                venue: $plan['venue'],
                score: $plan['quarterfinal_scores'][2],
            ),
            $this->createFinishedKnockoutMatch(
                ageGroupId: $ageGroup->id,
                roundOrder: 1,
                slot: 4,
                roundLabel: 'Perempat Final',
                matchDay: $ageGroup->code.' Perempat Final 4',
                clubA: $clubs[2],
                clubB: $clubs[5],
                matchDate: $baseDate->copy()->addDay(),
                kickoff: '11:00',
                venue: $plan['venue'],
                score: $plan['quarterfinal_scores'][3],
            ),
        ];

        $semifinal1 = $this->createFinishedKnockoutMatch(
            ageGroupId: $ageGroup->id,
            roundOrder: 2,
            slot: 1,
            roundLabel: 'Semifinal',
            matchDay: $ageGroup->code.' Semifinal 1',
            clubA: $quarterfinals[0]['winner'],
            clubB: $quarterfinals[1]['winner'],
            matchDate: $baseDate->copy()->addDays(3),
            kickoff: '14:00',
            venue: $plan['venue'],
            score: $plan['semifinal_scores'][0],
            sourceAId: $quarterfinals[0]['match']->id,
            sourceBId: $quarterfinals[1]['match']->id,
        );

        $semifinal2 = $this->createFinishedKnockoutMatch(
            ageGroupId: $ageGroup->id,
            roundOrder: 2,
            slot: 2,
            roundLabel: 'Semifinal',
            matchDay: $ageGroup->code.' Semifinal 2',
            clubA: $quarterfinals[2]['winner'],
            clubB: $quarterfinals[3]['winner'],
            matchDate: $baseDate->copy()->addDays(3),
            kickoff: '16:00',
            venue: $plan['venue'],
            score: $plan['semifinal_scores'][1],
            sourceAId: $quarterfinals[2]['match']->id,
            sourceBId: $quarterfinals[3]['match']->id,
        );

        $this->createFinishedKnockoutMatch(
            ageGroupId: $ageGroup->id,
            roundOrder: 3,
            slot: 1,
            roundLabel: 'Final',
            matchDay: $ageGroup->code.' Final',
            clubA: $semifinal1['winner'],
            clubB: $semifinal2['winner'],
            matchDate: $baseDate->copy()->addDays(5),
            kickoff: '16:00',
            venue: $plan['venue'],
            score: $plan['final_score'],
            sourceAId: $semifinal1['match']->id,
            sourceBId: $semifinal2['match']->id,
        );
    }

    /**
     * @return array<int, Club>
     */
    private function resolveClubs(array $clubNames): array
    {
        return array_map(fn (string $clubName) => $this->getClub($clubName), $clubNames);
    }

    /**
     * @return array{match: MatchSchedule, winner: Club}
     */
    private function createFinishedKnockoutMatch(
        int $ageGroupId,
        int $roundOrder,
        int $slot,
        string $roundLabel,
        string $matchDay,
        Club $clubA,
        Club $clubB,
        $matchDate,
        string $kickoff,
        string $venue,
        array $score,
        ?int $sourceAId = null,
        ?int $sourceBId = null,
    ): array {
        [$scoreA, $scoreB] = $score;

        if ($scoreA === $scoreB) {
            throw new \LogicException('Skor untuk '.$matchDay.' tidak boleh seri pada seeder knockout.');
        }

        $match = MatchSchedule::query()->create([
            'age_group_id' => $ageGroupId,
            'competition_format' => MatchSchedule::FORMAT_KNOCKOUT,
            'round_label' => $roundLabel,
            'round_order' => $roundOrder,
            'bracket_slot' => $slot,
            'source_match_a_id' => $sourceAId,
            'source_match_b_id' => $sourceBId,
            'club_a_id' => $clubA->id,
            'club_b_id' => $clubB->id,
            'match_day' => $matchDay,
            'venue' => $venue,
            'match_date' => $matchDate->toDateString(),
            'kickoff_time' => $kickoff,
            'score_club_a' => $scoreA,
            'score_club_b' => $scoreB,
            'is_finished' => true,
        ]);

        return [
            'match' => $match,
            'winner' => $scoreA > $scoreB ? $clubA : $clubB,
        ];
    }

    private function bracketPlans(): array
    {
        return [
            'U10' => [
                'clubs' => [
                    'Garuda Muda FC',
                    'Elang Nusantara',
                    'Rajawali City',
                    'Harimau Selatan FC',
                    'Cendrawasih United',
                    'Bintang Timur FC',
                    'Laskar Minang',
                    'Satria Padang',
                ],
                'venue' => 'Lapangan Mini Pariaman',
                'start_offset_days' => 12,
                'quarterfinal_scores' => [[2, 0], [1, 0], [3, 1], [1, 2]],
                'semifinal_scores' => [[2, 1], [1, 0]],
                'final_score' => [2, 1],
            ],
            'U12' => [
                'clubs' => [
                    'Garuda Muda FC',
                    'Elang Nusantara',
                    'Rajawali City',
                    'Harimau Selatan FC',
                    'Cendrawasih United',
                    'Bintang Timur FC',
                    'Mutiara Selatan',
                    'Singa Laut FC',
                ],
                'venue' => 'Stadion Liga Anak Piaman Laweh',
                'start_offset_days' => 15,
                'quarterfinal_scores' => [[1, 0], [2, 1], [0, 2], [3, 1]],
                'semifinal_scores' => [[1, 2], [1, 0]],
                'final_score' => [0, 1],
            ],
            'U14' => [
                'clubs' => [
                    'Elang Nusantara',
                    'Rajawali City',
                    'Garuda Muda FC',
                    'Mutiara Selatan',
                    'Satria Padang',
                    'Singa Laut FC',
                    'Cendrawasih United',
                    'Laskar Minang',
                ],
                'venue' => 'Rajawali Arena',
                'start_offset_days' => 18,
                'quarterfinal_scores' => [[2, 1], [1, 3], [2, 0], [1, 0]],
                'semifinal_scores' => [[1, 0], [1, 2]],
                'final_score' => [1, 2],
            ],
            'U16' => [
                'clubs' => [
                    'Garuda Muda FC',
                    'Rajawali City',
                    'Elang Nusantara',
                    'Bintang Timur FC',
                    'Harimau Selatan FC',
                    'Mutiara Selatan',
                    'Satria Padang',
                    'Singa Laut FC',
                ],
                'venue' => 'Stadion Utama Piaman Laweh',
                'start_offset_days' => 21,
                'quarterfinal_scores' => [[3, 0], [2, 1], [1, 0], [2, 0]],
                'semifinal_scores' => [[1, 0], [1, 2]],
                'final_score' => [2, 1],
            ],
        ];
    }
}
