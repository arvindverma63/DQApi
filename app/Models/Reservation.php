<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservation'; // Table name


    protected $fillable = [
        'restaurantId',
        'startTime',
        'endTime',
        'customerId',
        'payment',
        'advance',
        'notes',
        'created_at',
        'updated_at',
        'tableNumber',
    ];
}
