<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'customerId',
        'short',
        'feedback',
    ];

    // Enable timestamps if they exist in the table
    public $timestamps = true;

    // Define casts if needed
    protected $casts = [
        'customerId' => 'integer',
    ];

    // Define relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }
}
