<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'privacy' => 'public',
            'type' => 'file',
            'game' => fake()->optional()->word(),
            'campaign' => fake()->optional()->word(),
            'author' => fake()->optional()->name(),
            'tags' => [],
        ];
    }
}