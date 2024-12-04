<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'items',
        'tax',
        'discount',
        'sub_total',
        'total',
        'payment_type',
        'restaurantId',
        'addedBy',
        'tableNumber',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'items' => 'array', // Automatically cast the 'items' column to an array
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relationship to the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}
