<?php

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Player;
use App\Models\PlayerAgeGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires the new player submission fields', function () {
    $user = User::factory()->create([
        'role' => 'club',
    ]);

    $club = Club::query()->create([
        'user_id' => $user->id,
        'name' => 'Simpang FC',
        'verification_status' => Club::STATUS_DRAFT,
    ]);

    $ageGroup = AgeGroup::competition()->firstOrFail();

    $player = Player::query()->create([
        'club_id' => $club->id,
        'primary_age_group_id' => $ageGroup->id,
        'name' => 'Budi Santoso',
        'birth_place' => 'Padang',
        'birth_date' => '2014-05-10',
        'citizenship' => 'WNI',
        'photo_path' => 'players/photos/budi.jpg',
        'birth_certificate_file_path' => 'players/birth-certificates/budi.pdf',
        'family_card_file_path' => 'players/family-cards/budi.pdf',
        'verification_status' => Player::STATUS_DRAFT,
    ]);

    PlayerAgeGroup::query()->create([
        'player_id' => $player->id,
        'age_group_id' => $ageGroup->id,
        'season' => (string) date('Y'),
        'jersey_number' => 7,
        'position' => 'ST',
        'registration_status' => Player::STATUS_DRAFT,
        'status_date' => now(),
        'is_starter' => true,
        'is_substitute' => false,
    ]);

    $response = $this->actingAs($user)->post(route('players.submit', $player));

    $response->assertSessionHasErrors([
        'mother_name',
        'school_name',
        'diploma_file_path',
        'report_file_path',
    ]);

    expect($player->refresh()->verification_status)->toBe(Player::STATUS_DRAFT);
});

it('rejects inactive age groups on player submission', function () {
    $user = User::factory()->create([
        'role' => 'club',
    ]);

    $club = Club::query()->create([
        'user_id' => $user->id,
        'name' => 'Simpang FC',
        'verification_status' => Club::STATUS_DRAFT,
    ]);

    $inactiveAgeGroup = AgeGroup::query()->create([
        'name' => 'U-18',
        'code' => 'U18',
        'min_age' => 16,
        'max_age' => 18,
        'is_active' => false,
    ]);

    $player = Player::query()->create([
        'club_id' => $club->id,
        'primary_age_group_id' => $inactiveAgeGroup->id,
        'name' => 'Budi Santoso',
        'mother_name' => 'Siti Aminah',
        'school_name' => 'SDN 01',
        'birth_place' => 'Padang',
        'birth_date' => '2014-05-10',
        'citizenship' => 'WNI',
        'photo_path' => 'players/photos/budi.jpg',
        'birth_certificate_file_path' => 'players/birth-certificates/budi.pdf',
        'family_card_file_path' => 'players/family-cards/budi.pdf',
        'diploma_file_path' => 'players/diplomas/budi.pdf',
        'report_file_path' => 'players/reports/budi.pdf',
        'verification_status' => Player::STATUS_DRAFT,
    ]);

    PlayerAgeGroup::query()->create([
        'player_id' => $player->id,
        'age_group_id' => $inactiveAgeGroup->id,
        'season' => (string) date('Y'),
        'jersey_number' => 7,
        'position' => 'ST',
        'registration_status' => Player::STATUS_DRAFT,
        'status_date' => now(),
        'is_starter' => true,
        'is_substitute' => false,
    ]);

    $response = $this->actingAs($user)->post(route('players.submit', $player));

    $response->assertSessionHasErrors(['age_registrations']);
    expect($player->refresh()->verification_status)->toBe(Player::STATUS_DRAFT);
});
