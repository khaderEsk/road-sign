<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_road_sign', function (Blueprint $table) {
            $table->unsignedInteger('number_of_reserved_panels')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_road_sign', function (Blueprint $table) {
            $table->dropColumn('number_of_reserved_panels');
        });
    }
};
