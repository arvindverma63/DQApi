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
        Schema::table('orders', function (Blueprint $table) {
            // Adding an enum column with 'accept', 'reject', 'complete', and setting default to 'processing'
            $table->enum('status', ['processing', 'accept', 'reject', 'complete'])->default('processing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Dropping the status column when rolling back
            $table->dropColumn('status');
        });
    }
};
