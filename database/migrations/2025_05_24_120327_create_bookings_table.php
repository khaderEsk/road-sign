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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('broker_id')->nullable()->constrained('brokers')->onDelete('cascade');
            $table->unsignedTinyInteger('type');
            $table->date('start_date');
            $table->date('end_date');
            $table->float('total_advertising_space', 2)->nullable();
            $table->float('total_printing_space', 2)->nullable();
            $table->float('total_price', 2)->nullable();
            $table->integer('number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
