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
        Schema::create('road_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->string('place');
            $table->boolean('is_available')->default(true);
            $table->integer('faces_number');
            $table->string('number')->nullable();
            $table->string('directions');
            $table->decimal('advertising_meters', 8, 2);
            $table->decimal('printing_meters', 8, 2);
            $table->double('latitudeX');
            $table->double('longitudeY');
            $table->string('img');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('road_signs');
    }
};
