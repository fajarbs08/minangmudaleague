<?php

use App\Models\Club;
use App\Models\Official;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows approved player public pages with canonical id and slug urls', function () {
    $club = Club::query()->create([
        'name' => 'Simpang FC',
    ]);

    $player = Player::query()->create([
        'club_id' => $club->id,
        'name' => 'Budi Santoso',
        'school_name' => 'SD Negeri 01 Pariaman',
        'birth_place' => 'Pariaman',
        'birth_date' => '2014-01-10',
        'position' => 'Penyerang',
        'jersey_number' => 10,
        'verification_status' => Player::STATUS_APPROVED,
    ]);

    $this->get(route('public.players.show', ['playerSlug' => $player->public_slug]))
        ->assertOk()
        ->assertSee('Verifikasi Publik', false)
        ->assertSee('Budi Santoso', false)
        ->assertSee('Simpang FC', false)
        ->assertSee('Penyerang', false)
        ->assertSee('10', false)
        ->assertDontSee('SD Negeri 01 Pariaman', false)
        ->assertDontSee('Pariaman', false)
        ->assertDontSee('Tempat, Tanggal Lahir', false)
        ->assertDontSee('Sekolah', false);
});

it('redirects approved legacy player scan urls to the canonical public url', function () {
    $club = Club::query()->create([
        'name' => 'Simpang FC',
    ]);

    $player = Player::query()->create([
        'club_id' => $club->id,
        'name' => 'Budi Santoso',
        'verification_status' => Player::STATUS_APPROVED,
    ]);

    $this->get(route('players.scan-result', $player))
        ->assertRedirect(route('public.players.show', ['playerSlug' => $player->public_slug]));
});

it('rejects unapproved player public pages', function () {
    $club = Club::query()->create([
        'name' => 'Simpang FC',
    ]);

    $player = Player::query()->create([
        'club_id' => $club->id,
        'name' => 'Budi Santoso',
        'verification_status' => Player::STATUS_DRAFT,
    ]);

    $this->get(route('public.players.show', ['playerSlug' => $player->public_slug]))
        ->assertNotFound();
});

it('shows approved official public pages with canonical id and slug urls', function () {
    $club = Club::query()->create([
        'name' => 'Simpang FC',
    ]);

    $official = Official::query()->create([
        'club_id' => $club->id,
        'name' => 'Andi Pratama',
        'role' => 'Coach',
        'email' => 'andi@example.com',
        'phone' => '08123456789',
        'identity_number' => '1234567890123456',
        'license_levels' => 'Lisensi C',
        'verification_status' => Official::STATUS_APPROVED,
    ]);

    $this->get(route('public.officials.show', ['officialSlug' => $official->public_slug]))
        ->assertOk()
        ->assertSee('Verifikasi Publik', false)
        ->assertSee('Andi Pratama', false)
        ->assertSee('Simpang FC', false)
        ->assertSee('Coach', false)
        ->assertSee('Lisensi C', false)
        ->assertDontSee('andi@example.com', false)
        ->assertDontSee('08123456789', false)
        ->assertDontSee('1234567890123456', false)
        ->assertDontSee('Email', false)
        ->assertDontSee('Telepon', false)
        ->assertDontSee('Nomor Identitas', false);
});

it('redirects approved legacy official scan urls to the canonical public url', function () {
    $club = Club::query()->create([
        'name' => 'Simpang FC',
    ]);

    $official = Official::query()->create([
        'club_id' => $club->id,
        'name' => 'Andi Pratama',
        'role' => 'Coach',
        'verification_status' => Official::STATUS_APPROVED,
    ]);

    $this->get(route('officials.scan-result', $official))
        ->assertRedirect(route('public.officials.show', ['officialSlug' => $official->public_slug]));
});

it('rejects unapproved official public pages', function () {
    $club = Club::query()->create([
        'name' => 'Simpang FC',
    ]);

    $official = Official::query()->create([
        'club_id' => $club->id,
        'name' => 'Andi Pratama',
        'role' => 'Coach',
        'verification_status' => Official::STATUS_DRAFT,
    ]);

    $this->get(route('public.officials.show', ['officialSlug' => $official->public_slug]))
        ->assertNotFound();
});
