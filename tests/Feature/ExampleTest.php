<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_resources(): void
    {
        $this->get('/')
            ->assertRedirect(route('resources.index'));
    }

    public function test_public_resources_page_is_accessible(): void
    {
        $this->get(route('resources.index'))
            ->assertOk()
            ->assertViewIs('resources.index');
    }
}
