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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->unsignedBigInteger('user_id'); // Foreign key reference to the users table
            $table->json('items'); // Store items as JSON for flexibility
            $table->decimal('tax', 10, 2)->default(0); // Specify precision and set a default value
            $table->decimal('discount', 10, 2)->default(0); // Specify precision and set a default value
            $table->decimal('sub_total', 10, 2); // Subtotal amount with precision
            $table->decimal('total', 10, 2); // Total amount with precision
            $table->timestamps();

            // Foreign key constraint (if users table exists)
            $table->foreign('user_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
