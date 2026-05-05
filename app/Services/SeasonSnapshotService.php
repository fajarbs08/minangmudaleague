<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Official;
use App\Models\OfficialAgeGroup;
use App\Models\Player;
use App\Models\PlayerAgeGroup;
use App\Models\Season;
use App\Models\SeasonClub;
use App\Models\SeasonOfficial;
use App\Models\SeasonPlayer;
use Illuminate\Support\Collection;

class SeasonSnapshotService
{
    public function __construct(private SeasonContext $seasonContext) {}

    public function syncClubSnapshot(Club $club, ?Season $season = null): SeasonClub
    {
        $season ??= $this->seasonContext->requireActive();

        return SeasonClub::query()->updateOrCreate(
            [
                'season_id' => $season->id,
                'club_id' => $club->id,
            ],
            [
                'user_id' => $club->user_id,
                'name' => $club->name,
                'short_name' => $club->short_name,
                'manager_name' => $club->manager_name,
                'manager_title' => $club->manager_title,
                'zone' => $club->zone,
                'founded_year' => $club->founded_year,
                'logo_url' => $club->logo_url,
                'statement_file_path' => $club->statement_file_path,
                'address' => $club->address,
                'training_address' => $club->training_address,
                'notes' => $club->notes,
                'verification_status' => $club->verification_status,
                'verification_notes' => $club->verification_notes,
                'submitted_at' => $club->submitted_at,
                'reviewed_by' => $club->reviewed_by,
                'reviewed_at' => $club->reviewed_at,
                'snapshot_source_updated_at' => $club->updated_at,
            ]
        );
    }

    public function syncPlayerSnapshot(Player $player, ?Season $season = null): SeasonPlayer
    {
        $season ??= $this->seasonContext->requireActive();
        $player->loadMissing(['club', 'ageRegistrations.ageGroup']);

        $seasonClub = $player->club ? $this->syncClubSnapshot($player->club, $season) : null;
        $registrations = $this->playerRegistrationSnapshot($player, $season->id);

        return SeasonPlayer::query()->updateOrCreate(
            [
                'season_id' => $season->id,
                'player_id' => $player->id,
            ],
            [
                'season_club_id' => $seasonClub?->id,
                'club_id' => $player->club_id,
                'primary_age_group_id' => $player->primary_age_group_id,
                'name' => $player->name,
                'mother_name' => $player->mother_name,
                'school_name' => $player->school_name,
                'jersey_number' => $player->jersey_number,
                'position' => $player->position,
                'citizenship' => $player->citizenship,
                'birth_place' => $player->birth_place,
                'birth_date' => $player->birth_date,
                'height_cm' => $player->height_cm,
                'weight_kg' => $player->weight_kg,
                'dominant_foot' => $player->dominant_foot,
                'is_captain' => $player->is_captain,
                'photo_path' => $player->photo_path,
                'diploma_file_path' => $player->diploma_file_path,
                'report_file_path' => $player->report_file_path,
                'birth_certificate_file_path' => $player->birth_certificate_file_path,
                'family_card_file_path' => $player->family_card_file_path,
                'notes' => $player->notes,
                'verification_status' => $player->verification_status,
                'verification_notes' => $player->verification_notes,
                'submitted_at' => $player->submitted_at,
                'reviewed_by' => $player->reviewed_by,
                'reviewed_at' => $player->reviewed_at,
                'registered_age_group_ids' => $registrations->pluck('age_group_id')->values()->all(),
                'age_registration_snapshot' => $registrations->values()->all(),
                'snapshot_source_updated_at' => $player->updated_at,
            ]
        );
    }

    public function syncOfficialSnapshot(Official $official, ?Season $season = null): SeasonOfficial
    {
        $season ??= $this->seasonContext->requireActive();
        $official->loadMissing(['club', 'ageRegistrations.ageGroup']);

        $seasonClub = $official->club ? $this->syncClubSnapshot($official->club, $season) : null;
        $registrations = $this->officialRegistrationSnapshot($official, $season->id);

        return SeasonOfficial::query()->updateOrCreate(
            [
                'season_id' => $season->id,
                'official_id' => $official->id,
            ],
            [
                'season_club_id' => $seasonClub?->id,
                'club_id' => $official->club_id,
                'age_group_id' => $official->age_group_id,
                'name' => $official->name,
                'role' => $official->role,
                'phone' => $official->phone,
                'email' => $official->email,
                'birth_place' => $official->birth_place,
                'citizenship' => $official->citizenship,
                'identity_number' => $official->identity_number,
                'birth_date' => $official->birth_date,
                'license_number' => $official->license_number,
                'license_levels' => $official->license_levels,
                'photo_path' => $official->photo_path,
                'license_file_path' => $official->license_file_path,
                'identity_file_path' => $official->identity_file_path,
                'is_active' => $official->is_active,
                'notes' => $official->notes,
                'verification_status' => $official->verification_status,
                'verification_notes' => $official->verification_notes,
                'submitted_at' => $official->submitted_at,
                'reviewed_by' => $official->reviewed_by,
                'reviewed_at' => $official->reviewed_at,
                'registered_age_group_ids' => $registrations->pluck('age_group_id')->values()->all(),
                'age_registration_snapshot' => $registrations->values()->all(),
                'snapshot_source_updated_at' => $official->updated_at,
            ]
        );
    }

    public function seasonClubIdForClub(int $clubId, ?int $seasonId = null): ?int
    {
        $season = $this->resolveSeason($seasonId);
        $snapshot = SeasonClub::query()
            ->where('season_id', $season->id)
            ->where('club_id', $clubId)
            ->first();

        if ($snapshot) {
            return $snapshot->id;
        }

        $club = Club::query()->find($clubId);

        return $club ? $this->syncClubSnapshot($club, $season)->id : null;
    }

    public function seasonPlayerIdForPlayer(int $playerId, ?int $seasonId = null): ?int
    {
        $season = $this->resolveSeason($seasonId);
        $snapshot = SeasonPlayer::query()
            ->where('season_id', $season->id)
            ->where('player_id', $playerId)
            ->first();

        if ($snapshot) {
            return $snapshot->id;
        }

        $player = Player::query()->with(['club', 'ageRegistrations.ageGroup'])->find($playerId);

        return $player ? $this->syncPlayerSnapshot($player, $season)->id : null;
    }

    private function resolveSeason(?int $seasonId): Season
    {
        if ($seasonId) {
            return Season::query()->findOrFail($seasonId);
        }

        return $this->seasonContext->requireActive();
    }

    private function playerRegistrationSnapshot(Player $player, int $seasonId): Collection
    {
        $registrations = PlayerAgeGroup::query()
            ->with('ageGroup')
            ->where('player_id', $player->id)
            ->forSeason($seasonId)
            ->orderBy('age_group_id')
            ->get();

        return $registrations
            ->filter(fn (PlayerAgeGroup $registration) => (int) ($registration->season_id ?? 0) === $seasonId)
            ->values()
            ->map(fn (PlayerAgeGroup $registration) => [
                'age_group_id' => $registration->age_group_id,
                'age_group_name' => $registration->ageGroup?->name,
                'age_group_code' => $registration->ageGroup?->code,
                'season' => $registration->season,
                'season_id' => $registration->season_id,
                'jersey_number' => $registration->jersey_number,
                'position' => $registration->position,
                'registration_status' => $registration->registration_status,
                'status_date' => optional($registration->status_date)?->toAtomString(),
                'notes' => $registration->notes,
                'is_starter' => (bool) $registration->is_starter,
                'is_substitute' => (bool) $registration->is_substitute,
            ]);
    }

    private function officialRegistrationSnapshot(Official $official, int $seasonId): Collection
    {
        $registrations = OfficialAgeGroup::query()
            ->with('ageGroup')
            ->where('official_id', $official->id)
            ->forSeason($seasonId)
            ->orderBy('age_group_id')
            ->get();

        return $registrations
            ->filter(fn (OfficialAgeGroup $registration) => (int) ($registration->season_id ?? 0) === $seasonId)
            ->values()
            ->map(fn (OfficialAgeGroup $registration) => [
                'age_group_id' => $registration->age_group_id,
                'age_group_name' => $registration->ageGroup?->name,
                'age_group_code' => $registration->ageGroup?->code,
                'season' => $registration->season,
                'season_id' => $registration->season_id,
                'role' => $registration->role,
                'license_levels' => $registration->license_levels,
                'registration_status' => $registration->registration_status,
                'status_date' => optional($registration->status_date)?->toAtomString(),
                'notes' => $registration->notes,
            ]);
    }
}
