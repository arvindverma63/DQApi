<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string|null $firstName
 * @property string|null $lastName
 * @property string|null $gender
 * @property string|null $restName
 * @property string|null $image
 * @property int|null $phoneNumber
 * @property string|null $address
 * @property string|null $pinCode
 * @property string $restaurantId
 * @property int $userId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $identity
 * @property string|null $identityNumber
 * @property string $email
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereIdentity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereIdentityNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile wherePinCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereRestName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserProfile whereUserId($value)
 * @mixin \Eloquent
 */
class UserProfile extends Model
{
    protected $table = 'user_profile';
    protected $fillable = ['firstName',
    'lastName',
    'email',
    'userId',
    'address',
    'phoneNumber',
    'pinCode',
    'restName',
    'restaurantId',
    'gender',
    'identity',
    'identityNumber'];
    use HasFactory;
}
