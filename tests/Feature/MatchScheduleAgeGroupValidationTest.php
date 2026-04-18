<?php

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects inactive age groups when storing a match schedule', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $clubA = Club::query()->create(['name' => 'Club A']);
    $clubB = Club::query()->create(['name' => 'Club B']);

    $inactiveAgeGroup = AgeGroup::query()->create([
        'name' => 'U-18',
        'code' => 'U18',
        'min_age' => 16,
        'max_age' => 18,
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)->post(route('matches.store'), [
        'age_group_id' => $inactiveAgeGroup->id,
        'competition_format' => 'league',
        'club_a_id' => $clubA->id,
        'club_b_id' => $clubB->id,
        'match_day' => 'Pekan 1',
        'venue' => 'Stadion Utama',
        'match_date' => now()->addDay()->toDateString(),
        'kickoff_time' => '15:00',
    ]);

    $response->assertSessionHasErrors(['age_group_id']);
});
