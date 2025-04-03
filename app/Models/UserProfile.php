<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    'fcm',
    'identityNumber'];
    use HasFactory;
}
