<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonClub;
use App\Models\SeasonPlayer;
use App\Models\User;
use App\Services\SeasonContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistoricalSeasonAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_views_use_snapshot_owner_instead_of_live_club_owner(): void
    {
        [$oldOwner, $newOwner, $archivedSeason, $club, $player] = $this->createHistoricalOwnershipScenario();

        $this->actingAs($oldOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('players.index'))
            ->assertOk()
            ->assertSee('Pemain Histori');

        $this->actingAs($newOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('players.index'))
            ->assertOk()
            ->assertDontSee('Pemain Histori');

        $this->actingAs($oldOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('players.show', $player))
            ->assertOk()
            ->assertSee('Pemain Histori');

        $this->actingAs($newOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('players.show', $player))
            ->assertNotFound();

        $this->actingAs($oldOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('clubs.show', $club))
            ->assertOk()
            ->assertSee('Klub Histori Lama');

        $this->actingAs($newOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('clubs.show', $club))
            ->assertNotFound();
    }

    public function test_history_document_downloads_use_snapshot_owner_instead_of_live_club_owner(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('players/diplomas/history.pdf', 'history diploma');
        Storage::disk('local')->put('clubs/statements/history.pdf', 'history statement');

        [$oldOwner, $newOwner, $archivedSeason, $club, $player] = $this->createHistoricalOwnershipScenario([
            'player' => ['diploma_file_path' => 'players/diplomas/history.pdf'],
            'club' => ['statement_file_path' => 'clubs/statements/history.pdf'],
        ]);

        $this->actingAs($oldOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('players.documents.download', [$player, 'diploma']))
            ->assertOk();

        $this->actingAs($newOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('players.documents.download', [$player, 'diploma']))
            ->assertNotFound();

        $this->actingAs($oldOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('clubs.statement.download', $club))
            ->assertOk();

        $this->actingAs($newOwner)
            ->withSession([SeasonContext::SESSION_KEY => $archivedSeason->id])
            ->get(route('clubs.statement.download', $club))
            ->assertNotFound();
    }

    private function createHistoricalOwnershipScenario(array $overrides = []): array
    {
        $archivedSeason = Season::create([
            'name' => 'Musim Histori Test',
            'slug' => 'musim-histori-test',
            'status' => Season::STATUS_ARCHIVED,
            'is_active' => null,
            'archived_at' => now(),
        ]);

        $oldOwner = User::factory()->create([
            'role' => 'club',
            'is_active' => true,
        ]);
        $newOwner = User::factory()->create([
            'role' => 'club',
            'is_active' => true,
        ]);

        $club = Club::create([
            'user_id' => $newOwner->id,
            'name' => 'Klub Live Baru',
            'short_name' => 'KLB',
            'verification_status' => Club::STATUS_APPROVED,
        ]);

        $seasonClub = SeasonClub::create([
            'season_id' => $archivedSeason->id,
            'club_id' => $club->id,
            'user_id' => $oldOwner->id,
            'name' => 'Klub Histori Lama',
            'short_name' => 'KHL',
            'verification_status' => Club::STATUS_APPROVED,
        ] + ($overrides['club'] ?? []));

        $player = Player::create([
            'club_id' => $club->id,
            'name' => 'Pemain Live',
            'verification_status' => Player::STATUS_APPROVED,
        ]);

        SeasonPlayer::create([
            'season_id' => $archivedSeason->id,
            'season_club_id' => $seasonClub->id,
            'club_id' => $club->id,
            'player_id' => $player->id,
            'name' => 'Pemain Histori',
            'verification_status' => Player::STATUS_APPROVED,
            'registered_age_group_ids' => [],
            'age_registration_snapshot' => [],
        ] + ($overrides['player'] ?? []));

        return [$oldOwner, $newOwner, $archivedSeason, $club, $player];
    }
}
