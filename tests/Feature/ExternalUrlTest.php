<?php

namespace Tests\Feature;

use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExternalUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_accessible_external_pdf_is_registered_without_cloud_storage(): void
    {
        Http::fake(['example.com/*' => Http::response('', 200)]);
        Storage::fake('b2');

        $response = $this->post(route('resources.store'), [
            'title' => 'Manual externo',
            'privacy' => 'public',
            'external_url' => 'https://example.com/manual.pdf',
        ]);

        $response->assertRedirect(route('resources.create'));
        $this->assertDatabaseHas('resource_files', [
            'file_path_or_url' => 'https://example.com/manual.pdf',
            'is_external' => true,
            'file_type' => 'pdf',
        ]);
        $this->assertEmpty(Storage::disk('b2')->allFiles('resources'));
    }

    public function test_inaccessible_external_url_is_rejected(): void
    {
        Http::fake(['example.com/*' => Http::response('', 404)]);

        $this->from(route('resources.create'))
            ->post(route('resources.store'), [
                'title' => 'Enlace inválido',
                'privacy' => 'public',
                'external_url' => 'https://example.com/missing.pdf',
            ])
            ->assertRedirect(route('resources.create'))
            ->assertSessionHasErrors('external_url');

        $this->assertDatabaseCount('resources', 0);
    }

    public function test_duplicate_external_url_is_rejected(): void
    {
        Http::fake(['example.com/*' => Http::response('', 200)]);

        $payload = [
            'title' => 'Primer enlace',
            'privacy' => 'public',
            'external_url' => 'https://example.com/duplicate.pdf',
        ];

        $this->from(route('resources.create'))
            ->post(route('resources.store'), $payload)
            ->assertRedirect(route('resources.create'));
        $this->from(route('resources.create'))
            ->post(route('resources.store'), [
            ...$payload,
            'title' => 'Segundo enlace',
        ])->assertRedirect(route('resources.create'))
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('resources', 1);
    }
}