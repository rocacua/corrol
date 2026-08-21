<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_public_resources_but_not_private_resources(): void
    {
        Resource::factory()->create(['title' => 'Recurso público']);
        Resource::factory()->create([
            'title' => 'Recurso privado',
            'privacy' => 'private',
            'user_id' => User::factory(),
        ]);

        $this->get(route('resources.index'))
            ->assertOk()
            ->assertSee('Recurso público')
            ->assertDontSee('Recurso privado');
    }

    public function test_user_sees_own_private_resources_and_search_filters(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        Resource::factory()->create([
            'title' => 'Dragón rojo',
            'privacy' => 'private',
            'user_id' => $user->id,
            'game' => 'RuneQuest',
            'tags' => ['villano'],
        ]);
        Resource::factory()->create(['title' => 'Castillo público']);
        Resource::factory()->create([
            'title' => 'Dragón ajeno',
            'privacy' => 'private',
            'user_id' => User::factory(),
        ]);

        $this->actingAs($user)
            ->get(route('resources.index', ['q' => 'Dragón', 'game' => 'RuneQuest', 'tag' => 'villano']))
            ->assertOk()
            ->assertSee('Dragón rojo')
            ->assertDontSee('Castillo público')
            ->assertDontSee('Dragón ajeno');
    }
}