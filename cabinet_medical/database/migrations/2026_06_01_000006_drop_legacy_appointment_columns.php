<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('appointments', 'date')) {
                $table->dropColumn('date');
            }

            if (Schema::hasColumn('appointments', 'time')) {
                $table->dropColumn('time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('appointments', 'date')) {
                $table->date('date')->nullable()->after('service_id');
            }

            if (! Schema::hasColumn('appointments', 'time')) {
                $table->time('time')->nullable()->after('date');
            }
        });
    }
};
