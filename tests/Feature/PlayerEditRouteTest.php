<?php

use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to open the player edit page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $club = Club::query()->create([
        'name' => 'Test FC',
    ]);

    $player = Player::query()->create([
        'club_id' => $club->id,
        'name' => 'Budi Santoso',
        'verification_status' => Player::STATUS_DRAFT,
    ]);

    $this->actingAs($admin)
        ->get(route('players.edit', $player))
        ->assertOk()
        ->assertSee('Edit Pemain', false)
        ->assertSee('Budi Santoso', false);
});
