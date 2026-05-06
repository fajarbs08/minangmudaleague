<?php

namespace Tests\Feature;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\MatchSchedule;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClubAccountVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_club_account_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive-club@example.test',
            'role' => 'club',
            'is_active' => false,
            'password' => 'secret-123',
        ]);

        Club::create([
            'user_id' => $user->id,
            'name' => 'Klub Nonaktif Login',
            'short_name' => 'KNL',
            'verification_status' => Club::STATUS_APPROVED,
        ]);

        $response = $this->from('/masuk')->post('/masuk', [
            'email' => 'inactive-club@example.test',
            'password' => 'secret-123',
        ]);

        $response->assertRedirect('/masuk');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_club_is_hidden_from_active_public_views_but_finished_history_remains_visible(): void
    {
        $activeSeason = Season::query()->where('is_active', true)->firstOrFail();
        $ageGroup = AgeGroup::competition()->firstOrFail();

        $inactiveClub = $this->createApprovedClub('Klub Nonaktif Alpha', 'KNA', false);
        $activeClub = $this->createApprovedClub('Klub Aktif Bravo', 'KAB', true);
        $activeClubTwo = $this->createApprovedClub('Klub Aktif Charlie', 'KAC', true);
        $activeClubThree = $this->createApprovedClub('Klub Aktif Delta', 'KAD', true);

        $finishedKnockout = $this->createMatch([
            'season_id' => $activeSeason->id,
            'age_group_id' => $ageGroup->id,
            'competition_format' => MatchSchedule::FORMAT_KNOCKOUT,
            'club_a_id' => $inactiveClub->id,
            'club_b_id' => $activeClub->id,
            'match_day' => 'Knockout selesai',
            'venue' => 'Lapangan Utama',
            'match_date' => Carbon::today()->subDay(),
            'kickoff_time' => '09:00',
            'score_club_a' => 2,
            'score_club_b' => 1,
            'is_finished' => true,
            'round_label' => 'Perempat Final',
            'round_order' => 1,
            'bracket_slot' => 1,
        ]);

        $this->createMatch([
            'season_id' => $activeSeason->id,
            'age_group_id' => $ageGroup->id,
            'competition_format' => MatchSchedule::FORMAT_KNOCKOUT,
            'club_a_id' => $activeClubTwo->id,
            'club_b_id' => $activeClubThree->id,
            'match_day' => 'Knockout belum selesai',
            'venue' => 'Lapangan Utama',
            'match_date' => Carbon::today()->addDay(),
            'kickoff_time' => '11:00',
            'is_finished' => false,
            'round_label' => 'Perempat Final',
            'round_order' => 1,
            'bracket_slot' => 2,
        ]);

        $this->createMatch([
            'season_id' => $activeSeason->id,
            'age_group_id' => $ageGroup->id,
            'competition_format' => MatchSchedule::FORMAT_LEAGUE,
            'club_a_id' => $inactiveClub->id,
            'club_b_id' => $activeClub->id,
            'match_day' => 'Liga selesai',
            'venue' => 'Lapangan Liga',
            'match_date' => Carbon::today()->subDays(2),
            'kickoff_time' => '15:00',
            'score_club_a' => 1,
            'score_club_b' => 1,
            'is_finished' => true,
        ]);

        $this->createMatch([
            'season_id' => $activeSeason->id,
            'age_group_id' => $ageGroup->id,
            'competition_format' => MatchSchedule::FORMAT_LEAGUE,
            'club_a_id' => $inactiveClub->id,
            'club_b_id' => $activeClubTwo->id,
            'match_day' => 'Liga belum selesai',
            'venue' => 'Lapangan Liga',
            'match_date' => Carbon::today()->addDays(2),
            'kickoff_time' => '16:00',
            'is_finished' => false,
        ]);

        $this->get(route('public.clubs'))
            ->assertOk()
            ->assertDontSee($inactiveClub->name)
            ->assertSee($activeClub->name);

        $this->get(route('public.clubs.show', ['clubSlug' => $inactiveClub->public_slug]))
            ->assertNotFound();

        $this->get(route('public.schedule'))
            ->assertOk()
            ->assertDontSee($inactiveClub->name)
            ->assertSee($activeClubTwo->name);

        $this->get(route('public.results'))
            ->assertOk()
            ->assertSee($inactiveClub->name)
            ->assertSee($activeClub->name);

        $this->get(route('public.results.show', ['matchSlug' => $finishedKnockout->public_slug]))
            ->assertOk()
            ->assertSee($inactiveClub->name)
            ->assertSee($activeClub->name);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee($inactiveClub->short_name);

        $this->get(route('public.brackets'))
            ->assertOk()
            ->assertSee($inactiveClub->name)
            ->assertSee($activeClubTwo->name)
            ->assertSee('Perempat Final');
    }

    public function test_reactivated_club_returns_to_active_public_visibility(): void
    {
        $activeSeason = Season::query()->where('is_active', true)->firstOrFail();
        $ageGroup = AgeGroup::competition()->firstOrFail();

        $reactivatedClub = $this->createApprovedClub('Klub Reaktivasi Echo', 'KRE', false);
        $activeClub = $this->createApprovedClub('Klub Aktif Foxtrot', 'KAF', true);

        $this->createMatch([
            'season_id' => $activeSeason->id,
            'age_group_id' => $ageGroup->id,
            'competition_format' => MatchSchedule::FORMAT_LEAGUE,
            'club_a_id' => $reactivatedClub->id,
            'club_b_id' => $activeClub->id,
            'match_day' => 'Liga mendatang reaktivasi',
            'venue' => 'Lapangan Reaktivasi',
            'match_date' => Carbon::today()->addDay(),
            'kickoff_time' => '10:00',
            'is_finished' => false,
        ]);

        $this->get(route('public.clubs'))
            ->assertOk()
            ->assertDontSee($reactivatedClub->name);

        $this->get(route('public.schedule'))
            ->assertOk()
            ->assertDontSee($reactivatedClub->name);

        $reactivatedClub->user->forceFill(['is_active' => true])->save();

        $this->get(route('public.clubs'))
            ->assertOk()
            ->assertSee($reactivatedClub->name);

        $this->get(route('public.clubs.show', ['clubSlug' => $reactivatedClub->public_slug]))
            ->assertOk()
            ->assertSee($reactivatedClub->name);

        $this->get(route('public.schedule'))
            ->assertOk()
            ->assertSee($reactivatedClub->name)
            ->assertSee($activeClub->name);
    }

    public function test_admin_can_toggle_club_account_status_via_dashboard_route(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $club = $this->createApprovedClub('Klub Toggle Golf', 'KTG', true);

        $this->actingAs($admin)
            ->patch(route('club-accounts.status', $club->user), [
                'is_active' => 0,
            ])
            ->assertRedirect(route('club-accounts.create'));

        $club->user->refresh();
        $this->assertFalse($club->user->is_active);

        $this->actingAs($admin)
            ->patch(route('club-accounts.status', $club->user), [
                'is_active' => 1,
            ])
            ->assertRedirect(route('club-accounts.create'));

        $club->user->refresh();
        $this->assertTrue($club->user->is_active);
    }

    private function createApprovedClub(string $name, string $shortName, bool $isActive): Club
    {
        $user = User::factory()->create([
            'name' => $name.' Manager',
            'email' => strtolower($shortName).'@example.test',
            'role' => 'club',
            'is_active' => $isActive,
            'password' => 'secret-123',
        ]);

        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'short_name' => $shortName,
            'verification_status' => Club::STATUS_APPROVED,
        ]);
    }

    private function createMatch(array $attributes): MatchSchedule
    {
        return MatchSchedule::query()->create($attributes + [
            'notes' => null,
            'season_id' => $attributes['season_id'] ?? Season::query()->where('is_active', true)->value('id'),
        ]);
    }
}
