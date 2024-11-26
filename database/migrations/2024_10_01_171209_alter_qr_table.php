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
        Schema::table('qr', function (Blueprint $table) {
            // Example of adding a new column
            $table->string('qrCodeUrl')->nullable();

            // Example of modifying an existing column
            $table->string('restaurantId', 100)->change();

            // Example of dropping a column
            // $table->dropColumn('qrImage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr', function (Blueprint $table) {
            // Reverting the added column
            $table->string('qrImage');

            // Reverting the change in column size
            $table->string('restaurantId');

            // If you dropped a column, you would need to add it back in the reverse migration
            // $table->string('qrImage')->nullable();
        });
    }
};
