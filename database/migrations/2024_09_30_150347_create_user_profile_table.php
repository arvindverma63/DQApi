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
        Schema::create('user_profile', function (Blueprint $table) {
            $table->id();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('gender')->nullable();
            $table->string('restName')->nullable();
            $table->string('image')->nullable();
            $table->integer('phoneNumber')->nullable();
            $table->string('address')->nullable();
            $table->string('pinCode')->nullable();
            $table->string('restaurantId');
            $table->unsignedBigInteger('userId'); // Ensure userId is unsigned and of the correct type
            $table->timestamps();

            // Foreign key constraint linking userId to users table id
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profile', function (Blueprint $table) {
            $table->dropForeign(['userId']); // Drop the foreign key before dropping the table
        });

        Schema::dropIfExists('user_profile');
    }
};
