<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $itemName
 * @property string $categoryId
 * @property string|null $itemImage
 * @property string $restaurantId
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $stock
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereItemImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = ['itemName',
    'id',
    'itemImage',
    'price',
    'categoryId',
    'restaurantId',
    'stock'];
    use HasFactory;

    public function stockItems()
{
    return $this->hasMany(MenuInventory::class, 'menuId');
}

}
