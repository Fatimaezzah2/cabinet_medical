<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('name');
            }

            if (! Schema::hasColumn('services', 'duration')) {
                $table->unsignedInteger('duration')->default(30)->after('price');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('appointments', 'appointment_date')) {
                $table->date('appointment_date')->nullable()->after('service_id');
            }

            if (! Schema::hasColumn('appointments', 'appointment_time')) {
                $table->time('appointment_time')->nullable()->after('appointment_date');
            }

            if (! Schema::hasColumn('appointments', 'total_price')) {
                $table->decimal('total_price', 10, 2)->default(0)->after('status');
            }
        });

        if (Schema::hasColumn('appointments', 'user_id')) {
            DB::table('appointments')
                ->whereNull('patient_id')
                ->update(['patient_id' => DB::raw('user_id')]);
        }

        if (Schema::hasColumn('appointments', 'date')) {
            DB::table('appointments')
                ->whereNull('appointment_date')
                ->update(['appointment_date' => DB::raw('date')]);
        }

        if (Schema::hasColumn('appointments', 'time')) {
            DB::table('appointments')
                ->whereNull('appointment_time')
                ->update(['appointment_time' => DB::raw('time')]);
        }

        DB::table('appointments')
            ->select('appointments.id', 'services.price')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->orderBy('appointments.id')
            ->each(function (object $appointment): void {
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['total_price' => $appointment->price]);
            });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'total_price')) {
                $table->dropColumn('total_price');
            }

            if (Schema::hasColumn('appointments', 'appointment_time')) {
                $table->dropColumn('appointment_time');
            }

            if (Schema::hasColumn('appointments', 'appointment_date')) {
                $table->dropColumn('appointment_date');
            }

            if (Schema::hasColumn('appointments', 'patient_id')) {
                $table->dropConstrainedForeignId('patient_id');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'duration')) {
                $table->dropColumn('duration');
            }

            if (Schema::hasColumn('services', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
