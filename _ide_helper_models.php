<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $categoryName
 * @property string|null $categoryImage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $restaurantId
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phoneNumber
 * @property string $restaurantId
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 */
	class Customer extends \Eloquent {}
}

namespace App\Models{
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
	class Inventory extends \Eloquent {}
}

namespace App\Models{
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuInventory> $stockItems
 * @property-read int|null $stock_items_count
 */
	class Menu extends \Eloquent {}
}

namespace App\Models{
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
	class MenuInventory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $tableNumber
 * @property string $restaurantId
 * @property array $orderDetails
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property int $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTableNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @mixin \Eloquent
 * @property int $notification
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNotification($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
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
 * @property string $tableNumber
 * @method static \Illuminate\Database\Eloquent\Builder|QrCode whereTableNumber($value)
 */
	class QrCode extends \Eloquent {}
}

namespace App\Models{
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
	class Supplier extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property array $items
 * @property string $tax
 * @property string $discount
 * @property string $sub_total
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payment_type
 * @property string $restaurantId
 * @property string $addedBy
 * @property-read \App\Models\Customer $user
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUserId($value)
 */
	class Transaction extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $role
 * @property string|null $otp
 * @property string|null $expire_at
 * @property string|null $restaurantId
 * @property int $status
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\UserProfile|null $userProfile
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent implements \Tymon\JWTAuth\Contracts\JWTSubject {}
}

namespace App\Models{
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
	class UserProfile extends \Eloquent {}
}

