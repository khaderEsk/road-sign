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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('type');
            $table->string('size');
            $table->decimal('advertising_space', 8, 2);
            $table->decimal('printing_space', 8, 2);
            // $table->decimal('printing_meter_price', 8, 2);
            // $table->decimal('advertising_meter_price', 8, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('appearance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
