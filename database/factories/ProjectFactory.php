<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();
        return [
            'slug' => Str::slug($name) . '-' . fake()->year(),
            'name' => $name . ' Demo',
            'client_name' => $name,
            'demo_password' => fake()->password(8),
            'created_by' => User::factory(),
            'status' => 'active',
        ];
    }
}
