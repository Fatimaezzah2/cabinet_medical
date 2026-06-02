<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'General Consultation',
                'Dental Care',
                'Eye Exam',
                'Blood Test',
                'Physical Therapy',
                'Cardiology Visit',
            ]),
            'price' => fake()->randomFloat(2, 150, 1200),
            'duration' => fake()->randomElement([15, 30, 45, 60, 90]),
            'description' => fake()->sentence(),
        ];
    }
}
