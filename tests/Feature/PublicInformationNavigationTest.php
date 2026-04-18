<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows footer contact links on the public homepage', function () {
    $response = $this->get(route('public.home'));

    $response->assertOk();
    $response->assertSee('id="footer-kontak"', false);
    $response->assertSee('082181761383', false);
    $response->assertSee('@liga.anakpariaman', false);
});

it('redirects legacy public information routes to the homepage footer contact', function () {
    $targetUrl = route('public.home').'#footer-kontak';

    $this->get(route('public.information'))
        ->assertRedirect($targetUrl);

    $this->get(route('public.information.show', ['resourceSlug' => 'dokumen-lama-1']))
        ->assertRedirect($targetUrl);

    $this->get('/informasi/file/1')
        ->assertRedirect($targetUrl);

    $this->get('/informasi/file/1/unduh')
        ->assertRedirect($targetUrl);
});
