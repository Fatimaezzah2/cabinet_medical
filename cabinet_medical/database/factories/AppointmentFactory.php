<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->patient(),
            'doctor_id' => User::factory()->doctor(),
            'service_id' => Service::factory(),
            'appointment_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'appointment_time' => fake()->time('H:i'),
            'status' => fake()->randomElement(Appointment::statuses()),
        ];
    }
}
