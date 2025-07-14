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
            $table->dropColumn([
                'is_available', 'faces_number',
                'advertising_meters', 'printing_meters','number'
            ]);
            $table->unsignedInteger('panels_number')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('road_signs', function (Blueprint $table) {
            $table->decimal('advertising_meters', 8, 2);
            $table->decimal('printing_meters', 8, 2);
            $table->boolean('is_available')->default(true);
            $table->integer('faces_number');
            $table->string('number')->nullable();
            $table->dropColumn('panels_number');
        });
    }
};
