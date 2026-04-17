<?php

use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('returns null for a missing local club logo path', function () {
    Storage::fake('public');

    $club = Club::query()->create([
        'name' => 'Garuda Muda FC',
        'short_name' => 'GM',
        'verification_status' => Club::STATUS_APPROVED,
        'logo_url' => 'club-logos/2026/04/missing-logo.png',
    ]);

    expect($club->logo_file_url)->toBeNull();
});

it('renders a fallback mark on the public clubs page when a club logo file is missing', function () {
    Storage::fake('public');

    Club::query()->create([
        'name' => 'Garuda Muda FC',
        'short_name' => 'GM',
        'verification_status' => Club::STATUS_APPROVED,
        'logo_url' => 'club-logos/2026/04/missing-logo.png',
    ]);

    $this->get('/klub')
        ->assertOk()
        ->assertSee('sponsor-mark', false)
        ->assertDontSee('/storage/club-logos/2026/04/missing-logo.png', false);
});
