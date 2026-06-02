<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        ActivityLog::query()->delete();
        Appointment::query()->delete();
        Service::query()->delete();
        User::query()->delete();

        Schema::enableForeignKeyConstraints();

        User::factory()
            ->admin()
            ->create([
                'name' => 'Fatima Admin',
                'email' => 'fatima@gmail.com',
                'password' => Hash::make('qwe123'),
            ]);

        $patients = User::factory()
            ->count(10)
            ->patient()
            ->create();

        $doctors = User::factory()
            ->count(5)
            ->doctor()
            ->create();

        $services = collect([
            Service::create([
                'name' => 'Consultation',
                'price' => 200,
                'duration' => 60,
                'description' => 'General medical consultation.',
            ]),
            Service::create([
                'name' => 'Cardiology',
                'price' => 400,
                'duration' => 60,
                'description' => 'Cardiology specialist appointment.',
            ]),
            Service::create([
                'name' => 'Dental',
                'price' => 250,
                'duration' => 60,
                'description' => 'Dental care appointment.',
            ]),
        ]);

        $slots = ['08:30', '09:30', '10:30', '11:30'];

        foreach ($doctors as $doctorIndex => $doctor) {
            foreach ($slots as $slotIndex => $slot) {
                Appointment::create([
                    'patient_id' => $patients[($doctorIndex * count($slots) + $slotIndex) % $patients->count()]->id,
                    'doctor_id' => $doctor->id,
                    'service_id' => $services[($doctorIndex + $slotIndex) % $services->count()]->id,
                    'appointment_date' => now()->addDays($doctorIndex + 1)->format('Y-m-d'),
                    'appointment_time' => $slot,
                    'status' => Appointment::STATUS_PENDING,
                ]);
            }
        }
    }
}
