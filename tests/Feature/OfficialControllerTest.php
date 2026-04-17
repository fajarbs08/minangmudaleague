<?php

use App\Http\Controllers\OfficialController;
use App\Models\Official;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(RefreshDatabase::class);

it('returns a not found response when editing an official without a club', function () {
    $user = User::factory()->create([
        'role' => 'club',
    ]);

    $this->actingAs($user);

    $controller = app(OfficialController::class);
    $official = new Official([
        'club_id' => null,
    ]);

    expect(fn () => $controller->edit($official))
        ->toThrow(NotFoundHttpException::class);
});
