<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $menuId
 * @property string $restaurantId
 * @property string $quantity
 * @property int $stockId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory query()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuInventory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MenuInventory extends Model
{
    protected $table = 'menu_inventory';
    protected $fillable = [
        'menuId',
        'restaurantId',
        'quantity',
        'stockId',
    ];
    public function stockItem()
    {
        return $this->belongsTo(Inventory::class, 'stockId', 'id');
    }
    use HasFactory;
}
