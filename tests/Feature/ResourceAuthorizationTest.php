<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_public_resource(): void
    {
        $resource = Resource::factory()->create([
            'privacy' => 'public',
        ]);

        $this->get(route('resources.show', $resource->id))
            ->assertOk();
    }

    public function test_guest_cannot_see_private_resource(): void
    {
        $resource = Resource::factory()->create([
            'privacy' => 'private',
            'user_id' => User::factory(),
        ]);

        $this->get(route('resources.show', $resource->id))
            ->assertForbidden();
    }

    public function test_owner_can_see_private_resource(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        $resource = Resource::factory()->create([
            'privacy' => 'private',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('resources.show', $resource->id))
            ->assertOk();
    }

    public function test_other_user_cannot_edit_resource(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $otherUser */
        $otherUser = User::factory()->createOne();

        $resource = Resource::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser)
            ->get(route('resources.edit', $resource->id))
            ->assertForbidden();
    }
}