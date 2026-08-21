<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreResourceValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_requires_file_or_external_url(): void
    {
        $response = $this->post(route('resources.store'), [
            'title' => 'Recurso sin archivo',
            'privacy' => 'public',
        ]);

        $response->assertSessionHasErrors(['file', 'external_url']);
    }

    public function test_title_is_required(): void
    {
        Http::fake([
            'example.com/*' => Http::response('', 200),
        ]);

        $response = $this->post(route('resources.store'), [
            'privacy' => 'public',
            'external_url' => 'https://example.com/file.pdf',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_guest_cannot_create_private_resource(): void
    {
        Http::fake([
            'example.com/*' => Http::response('', 200),
        ]);

        $response = $this->post(route('resources.store'), [
            'title' => 'Recurso público',
            'privacy' => 'private',
            'external_url' => 'https://example.com/file.pdf',
        ]);

        $response->assertRedirect(route('resources.create'));

        $this->assertDatabaseHas('resources', [
            'title' => 'Recurso público',
            'privacy' => 'public',
        ]);
    }
}