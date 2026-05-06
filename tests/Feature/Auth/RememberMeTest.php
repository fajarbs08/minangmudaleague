<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_remember_me_sets_recaller_cookie_and_token(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'remember_token' => null,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $response->assertCookieNotExpired(Auth::guard('web')->getRecallerName());
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_login_without_remember_me_does_not_set_recaller_cookie(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'remember_token' => null,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
        $this->assertNull($user->fresh()->remember_token);
    }
}
