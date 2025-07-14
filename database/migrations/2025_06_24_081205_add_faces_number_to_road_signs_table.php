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
        Schema::table('road_signs', function (Blueprint $table) {
            $table->unsignedBigInteger('faces_number')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('road_signs', function (Blueprint $table) {
            $table->dropColumn('faces_number');
        });
    }
};
