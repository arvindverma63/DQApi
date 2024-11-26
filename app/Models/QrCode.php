<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $qrImage
 * @property string $restaurantId
 * @property string|null $qrCodeUrl
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereQrCodeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereQrImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QrCode extends Model
{
    protected $table = 'qr';
    protected $fillable = ['restaurantId',
    'tableNumber',
    'qrImage',
    'qrCodeUrl'];
    use HasFactory;
}
