<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $supplierName
 * @property string $email
 * @property string $phoneNumber
 * @property string $rawItem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $restaurantId
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $inventories
 * @property-read int|null $inventories_count
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereRawItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereSupplierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplierName',
        'email',
        'phoneNumber',
        'rawItem',
        'restaurantId',
    ];

    // Define the relationship to the Inventory
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'supplierId');
    }
}
