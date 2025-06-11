<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Generate fake data for task model
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(rand(3, 8)),
            'status' => $this->faker->randomElement(['pending', 'in-progress', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ];
    }
}
