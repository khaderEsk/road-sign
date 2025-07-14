<?php

use App\ProductType;
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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('product_type')->nullable()->after('number');
            $table->longText('notes')->nullable()->after('product_type');
            $table->unsignedTinyInteger('discount_type')->nullable()->after('notes');
            $table->float('value', 2)->nullable()->after('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'notes', 'discount_type', 'value']);
        });
    }
};
