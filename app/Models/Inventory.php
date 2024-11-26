<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $itemName
 * @property string $quantity
 * @property int $supplierId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $restaurantId
 * @property string $unit
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'itemName',
        'quantity',
        'supplierId',
        'restaurantId',
        'unit'
    ];

    // Define the relationship to the Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierId');
    }
}
