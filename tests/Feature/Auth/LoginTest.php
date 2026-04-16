<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('renders the remember me field on the login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('name="remember"', false)
        ->assertSee('value="1"', false);
});

it('sets the recaller cookie when remember me is selected', function () {
    $user = User::factory()->create();
    $recallerName = Auth::guard('web')->getRecallerName();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
        'remember' => '1',
    ])
        ->assertRedirect()
        ->assertCookie($recallerName)
        ->assertCookieNotExpired($recallerName);
});

it('does not set the recaller cookie when remember me is not selected', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect()
        ->assertCookieMissing(Auth::guard('web')->getRecallerName());
});
