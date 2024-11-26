<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_inventory', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('menuId'); // Foreign key to the menu table
            $table->string('restaurantId'); // ID of the restaurant associated with this inventory
            $table->decimal('quantity', 8, 3); // Quantity with 3 decimal places for precision
            $table->unsignedBigInteger('stockId'); // Foreign key to the stock table
            $table->timestamps(); // created_at and updated_at

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_inventory');
    }
}
