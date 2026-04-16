<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('downloads the default statement template', function () {
    $user = User::factory()->create([
        'role' => 'club',
    ]);

    $this->actingAs($user)
        ->get(route('clubs.statement-template'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename=surat_pernyataan_liga_piaman_laweh_final.pdf');
});
