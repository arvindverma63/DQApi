<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DueTransactions extends Model
{
    use HasFactory;

    public $table = "due_transactions";

    public $fillable = [
        'transaction_id',
        'total',
        'status',
        'restaurantId'
    ];
}
