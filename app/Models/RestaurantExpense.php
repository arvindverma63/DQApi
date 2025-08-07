<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RestaurantExpense",
 *     type="object",
 *     title="Restaurant Expense",
 *     required={"restaurantId", "expense_date", "category", "amount"},
 *     @OA\Property(property="restaurantId", type="string"),
 *     @OA\Property(property="expense_date", type="string", format="date"),
 *     @OA\Property(property="category", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="payment_method", type="string"),
 *     @OA\Property(property="created_at", type="string"),
 *     @OA\Property(property="updated_at", type="string")
 * )
 */

class RestaurantExpense extends Model
{
    // If table name is not plural of model name
    protected $table = 'restaurant_expenses';

    // If you are not using Laravel's default timestamps
    public $timestamps = false;

    // If you manually handle created_at and updated_at as strings
    protected $fillable = [
        'restaurantId',
        'expense_date',
        'category',
        'description',
        'amount',
        'payment_method',
        'created_at',
        'updated_at'
    ];

    // Optionally cast amount as float or decimal
    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];
}
