<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the main public pages without seeded data', function () {
    $pages = [
        '/' => 'LIGA ANAK PIAMAN LAWEH',
        '/jadwal-pertandingan' => 'Jadwal belum tersedia',
        '/hasil-pertandingan' => 'Daftar Hasil Pertandingan',
        '/klasemen' => 'Klasemen dan bracket belum tersedia',
        '/klub' => 'Klub belum tersedia',
        '/sponsor' => 'Sponsor belum tersedia',
    ];

    foreach ($pages as $uri => $expectedText) {
        get($uri)
            ->assertOk()
            ->assertSee($expectedText, false);
    }
});
