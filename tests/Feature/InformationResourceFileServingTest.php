<?php

use App\Models\InformationResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('renders the custom 404 page when a public resource file is missing', function () {
    Storage::fake('public');

    $resource = InformationResource::query()->create([
        'title' => 'Regulasi Pendaftaran Liga',
        'category' => 'rules',
        'description' => 'Dokumen rules.',
        'file_path' => 'information-resources/regulasi-pendaftaran-liga.pdf',
        'file_name' => 'regulasi-pendaftaran-liga.pdf',
        'file_mime' => 'application/pdf',
        'visibility' => InformationResource::VISIBILITY_PUBLIC,
        'sort_order' => 1,
        'is_pinned' => false,
        'is_published' => true,
    ]);

    $this->get(route('information-resources.file', $resource))
        ->assertNotFound()
        ->assertSee('Page Not Found!', false)
        ->assertSee('/images/404.svg', false);
});

it('serves a public resource file through laravel', function () {
    Storage::fake('public');
    Storage::disk('public')->put('information-resources/regulasi-pendaftaran-liga.pdf', 'rules');

    $resource = InformationResource::query()->create([
        'title' => 'Regulasi Pendaftaran Liga',
        'category' => 'rules',
        'description' => 'Dokumen rules.',
        'file_path' => 'information-resources/regulasi-pendaftaran-liga.pdf',
        'file_name' => 'regulasi-pendaftaran-liga.pdf',
        'file_mime' => 'application/pdf',
        'visibility' => InformationResource::VISIBILITY_PUBLIC,
        'sort_order' => 1,
        'is_pinned' => false,
        'is_published' => true,
    ]);

    $this->get(route('information-resources.file', $resource))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('allows authenticated club users to download club-only resources through laravel', function () {
    Storage::fake('public');
    Storage::disk('public')->put('information-resources/template-surat-pernyataan-klub.pdf', 'template');

    $resource = InformationResource::query()->create([
        'title' => 'Template Surat Pernyataan Klub',
        'category' => 'template',
        'description' => 'Dokumen club.',
        'file_path' => 'information-resources/template-surat-pernyataan-klub.pdf',
        'file_name' => 'template-surat-pernyataan-klub.pdf',
        'file_mime' => 'application/pdf',
        'visibility' => InformationResource::VISIBILITY_CLUB,
        'sort_order' => 1,
        'is_pinned' => false,
        'is_published' => true,
    ]);

    $user = User::factory()->create([
        'role' => 'club',
    ]);

    $response = $this->actingAs($user)
        ->get(route('information-resources.download', $resource));

    $response
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->headers->get('content-disposition'))->toContain('attachment');
});
