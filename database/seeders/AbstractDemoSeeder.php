<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\LineupList;
use App\Models\MatchSchedule;
use App\Models\Official;
use App\Models\OfficialAgeGroup;
use App\Models\Player;
use App\Models\PlayerAgeGroup;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\SeasonContext;
use App\Services\SeasonSnapshotService;
use Database\Seeders\Concerns\SeedsDemoAssets;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

abstract class AbstractDemoSeeder extends Seeder
{
    use SeedsDemoAssets;

    protected function adminUser(): User
    {
        return User::where('email', 'admin@ligaanakpiamanlaweh.com')->firstOrFail();
    }

    protected function upsertUser(string $email, string $name, string $role, string $password): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'remember_token' => str()->random(10),
            ]
        );
    }

    protected function upsertClub(array $identity, array $attributes): Club
    {
        $club = Club::updateOrCreate($identity, $attributes);
        $this->seasonSnapshots()->syncClubSnapshot($club);

        return $club;
    }

    protected function upsertOfficial(Club $club, string $name, array $attributes): Official
    {
        $official = Official::updateOrCreate(
            ['club_id' => $club->id, 'name' => $name],
            $attributes + ['club_id' => $club->id]
        );

        if (! empty($attributes['age_group_id'])) {
            OfficialAgeGroup::updateOrCreate(
                [
                    'official_id' => $official->id,
                    'age_group_id' => $attributes['age_group_id'],
                    'season_id' => $this->activeSeasonId(),
                ],
                [
                    'season' => $this->activeSeasonName(),
                    'role' => $attributes['role'] ?? null,
                    'license_levels' => $attributes['license_levels'] ?? null,
                    'registration_status' => $attributes['verification_status'] ?? null,
                    'status_date' => $attributes['reviewed_at'] ?? $attributes['submitted_at'] ?? now(),
                    'notes' => null,
                ]
            );
        }

        $this->seasonSnapshots()->syncOfficialSnapshot($official->fresh(['club', 'ageRegistrations.ageGroup']));

        return $official;
    }

    protected function upsertPlayer(Club $club, string $name, array $attributes): Player
    {
        $player = Player::updateOrCreate(
            ['club_id' => $club->id, 'name' => $name],
            $attributes + ['club_id' => $club->id]
        );

        if (! empty($attributes['primary_age_group_id'])) {
            PlayerAgeGroup::updateOrCreate(
                [
                    'player_id' => $player->id,
                    'age_group_id' => $attributes['primary_age_group_id'],
                    'season_id' => $this->activeSeasonId(),
                ],
                [
                    'season' => $this->activeSeasonName(),
                    'jersey_number' => $attributes['jersey_number'] ?? null,
                    'position' => $attributes['position'] ?? null,
                    'registration_status' => $attributes['verification_status'] ?? null,
                    'status_date' => $attributes['reviewed_at'] ?? $attributes['submitted_at'] ?? now(),
                ]
            );
        }

        $this->seasonSnapshots()->syncPlayerSnapshot($player->fresh(['club', 'ageRegistrations.ageGroup']));

        return $player;
    }

    protected function upsertLineup(Club $club, string $title, array $attributes): LineupList
    {
        $seasonId = $this->activeSeasonId();

        return LineupList::updateOrCreate(
            ['season_id' => $seasonId, 'club_id' => $club->id, 'title' => $title],
            $attributes + [
                'season_id' => $seasonId,
                'club_id' => $club->id,
                'season_club_id' => $this->seasonSnapshots()->seasonClubIdForClub($club->id, $seasonId),
            ]
        );
    }

    protected function upsertMatchSchedule(array $identity, array $attributes): MatchSchedule
    {
        $seasonId = $this->activeSeasonId();

        return MatchSchedule::updateOrCreate(
            ['season_id' => $seasonId] + $identity,
            $attributes + $identity + [
                'season_id' => $seasonId,
                'club_a_season_id' => $this->seasonSnapshots()->seasonClubIdForClub((int) $identity['club_a_id'], $seasonId),
                'club_b_season_id' => $this->seasonSnapshots()->seasonClubIdForClub((int) $identity['club_b_id'], $seasonId),
            ]
        );
    }

    protected function upsertSponsor(array $identity, array $attributes): Sponsor
    {
        return Sponsor::updateOrCreate($identity, $attributes);
    }

    protected function syncLineupPlayers(LineupList $lineupList, array $entries): void
    {
        $syncData = [];

        foreach ($entries as $index => $entry) {
            if (! $entry['player']) {
                continue;
            }

            $syncData[$entry['player']->id] = [
                'role' => $entry['role'],
                'display_order' => $index + 1,
                'season_player_id' => $this->seasonSnapshots()->seasonPlayerIdForPlayer($entry['player']->id, $this->activeSeasonId()),
            ];
        }

        $lineupList->players()->sync($syncData);
    }

    protected function getClub(string $name): Club
    {
        return Club::where('name', $name)->firstOrFail();
    }

    protected function getAgeGroup(string $code): AgeGroup
    {
        return AgeGroup::where('code', $code)->firstOrFail();
    }

    protected function activeSeasonId(): int
    {
        return $this->seasonContext()->requireActive()->id;
    }

    protected function activeSeasonName(): string
    {
        return $this->seasonContext()->requireActive()->name;
    }

    protected function seasonContext(): SeasonContext
    {
        return app(SeasonContext::class);
    }

    protected function seasonSnapshots(): SeasonSnapshotService
    {
        return app(SeasonSnapshotService::class);
    }
}
