<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'country' => $this->faker->country(),
            'website' => null,
        ];
    }
}
