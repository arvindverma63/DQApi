<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryManagement extends Model
{
    use HasFactory;

    protected $table = 'delivery_management';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'restaurantId',
        'start_time',
        'end_time',
        'delivery_status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'delivery_status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public $timestamps = false;
}
