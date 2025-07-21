<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOperator extends Model
{
    use HasFactory;
    protected $table = 'bill_operators';
    protected $fillable = [
        'restaurantId',
        'operator',
    ];
}
