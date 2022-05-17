<?php

namespace Database\Factories;

use App\Models\Secret;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @extends Factory<Secret>
 */
class SecretFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['name' => "null", 'content' => "null"])]
    public function definition(): array
    {
        return [
            'name' => null,
            'content' => null,
        ];
    }
}
