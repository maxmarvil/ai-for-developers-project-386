<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['event_type_id', 'date', 'start_time']);
            $table->unique(['date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['date', 'start_time']);
            $table->unique(['event_type_id', 'date', 'start_time']);
        });
    }
};
