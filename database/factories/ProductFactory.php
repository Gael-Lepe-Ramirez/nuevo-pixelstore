<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    $name = $this->faker->unique()->sentence(3);
    return [
        'category_id' => \App\Models\Category::factory(),
        'name' => $name,
        'slug' => str($name)->slug(),
        'description' => $this->faker->paragraph(),
        'price' => $this->faker->randomFloat(2, 100, 5000),
        'stock' => $this->faker->numberBetween(0, 50),
        'is_active' => true,
    ];
}
}
