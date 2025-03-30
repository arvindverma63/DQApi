<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'delivery';

    protected $fillable = [
        'customer_id',
        'address_1',
        'address_2',
        'phone_number',
        'restaurantId',
        'pincode',
    ];
}
