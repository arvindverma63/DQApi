<?php

namespace App\Http\Controllers\WebAppControllers;

use App\Http\Controllers\AdminControllers\FirebaseNotificationController;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\MenuInventory;
use App\Models\Order;
use App\Models\UserProfile;
use Kreait\Firebase\Factory;
use App\Services\FirebaseService;
use DB;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Log;
use Notification;

class WebOrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/webMenu",
     *     summary="Get the menu items for a restaurant",
     *     description="Fetch all menu items for a specific restaurant by restaurant ID",
     *     operationId="getRestaurantMenu",
     *     tags={"WebAppMenu"},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         description="ID of the restaurant",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="number", example=1),
     *                 @OA\Property(property="itemName", type="string", example="Cheese Pizza"),
     *                 @OA\Property(property="itemImage", type="string", example="http://example.com/storage/app/public/item_image.jpg"),
     *                 @OA\Property(property="price", type="number", format="float", example=12.99),
     *                 @OA\Property(property="category", type="string", example="Pizza"),
     *                 @OA\Property(
     *                     property="ingredients",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="ingredientName", type="string", example="Mozzarella Cheese"),
     *                         @OA\Property(property="quantity", type="number", format="float", example=1.5),
     *                         @OA\Property(property="unit", type="string", example="kg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Missing or invalid parameter"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No menu found for the given restaurant ID"
     *     )
     * )
     */
    public function menu(Request $request)
    {
        // Validate the request to ensure restaurantId is provided.
        $request->validate([
            'restaurantId' => 'required|string',
        ]);

        // Fetch all menu items for the given restaurant
        $menuItems = Menu::where('restaurantId', $request['restaurantId'])
            ->where('status', 0)->get();

        if ($menuItems->isEmpty()) {
            return response()->json(['message' => 'No menu found for the given restaurant ID'], 404);
        }

        // Pre-fetch all categories and ingredients to optimize queries
        $categoryIds = $menuItems->pluck('categoryId')->unique();
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $menuIds = $menuItems->pluck('id')->unique();
        $menuInventories = MenuInventory::whereIn('menuId', $menuIds)->get();
        $stockIds = $menuInventories->pluck('stockId')->unique();
        $inventories = Inventory::whereIn('id', $stockIds)->get()->keyBy('id');

        $data = [];

        foreach ($menuItems as $menu) {
            // Correctly generate the public URL for the image stored in the 'public/menus' folder
            $menu->itemImage = $menu->itemImage ? url('menus/' . basename($menu->itemImage)) : null;

            $category = $categories->get($menu->categoryId);

            // Map ingredients for the current menu item
            $ingredients = $menuInventories->where('menuId', $menu->id)->map(function ($inventory) use ($inventories) {
                $ingredient = $inventories->get($inventory->stockId);
                return $ingredient ? [
                    'ingredientName' => $ingredient->itemName,
                    'quantity' => $inventory->quantity,
                    'unit' => $ingredient->unit,
                ] : null;
            })->filter()->values(); // Remove null and reindex

            // Add each menu item to the response data
            $data[] = [
                'id' => $menu->id,
                'itemName' => $menu->itemName,
                'itemImage' => $menu->itemImage,
                'price' => $menu->price,
                'category' => $category ? $category->categoryName : null,
                'ingredients' => $ingredients,
            ];
        }

        // Return data as JSON response
        return response()->json($data);
    }

    /**
     * Add a new transaction.
     *
     * @OA\Post(
     *     path="/addOrder",
     *     summary="Add a new transaction",
     *     description="Create a new transaction with user details, order details, and restaurant information.",
     *     operationId="addOrder",
     *     tags={"WebAppMenu"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="tableNumber", type="string", description="Table number", example="12"),
     *             @OA\Property(property="restaurantId", type="string", description="Restaurant ID", example="R1728231298"),
     *            @OA\Property(
     *                 property="orderDetails",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "itemName", "category", "price", "ingredients", "imageUrl", "quantity"},
     *                     @OA\Property(property="id", type="string", example="204"),
     *                     @OA\Property(property="itemName", type="string", example="Tanddori Momos"),
     *                     @OA\Property(property="category", type="string", example="MOMOS"),
     *                     @OA\Property(property="price", type="number", format="float", example=120.0),
     *                     @OA\Property(
     *                         property="ingredients",
     *                         type="array",
     *                         @OA\Items(type="string", example="MAIDA")
     *                     ),
     *                     @OA\Property(property="imageUrl", type="string", example="https://rest.dicui.org/menus/1733563865_download%20(56).jpeg"),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(property="phoneNumber", type="string", description="Customer phone number", example="9876543210"),
     *             @OA\Property(property="userName", type="string", description="Customer name", example="John Doe"),
     *             @OA\Property(property="email", type="string", description="Customer email", example="john.doe@example.com"),
     *             @OA\Property(property="address", type="string", description="Customer address", example="123 Main St"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Transaction"),
     *             @OA\Property(property="message", type="string", example="Transaction created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid input data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */

    public function addTransaction(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate the incoming request
            $validated = $request->validate([
                'tableNumber' => 'nullable|string',
                'restaurantId' => 'required|string',
                'orderDetails' => 'required',
                'phoneNumber' => 'nullable|string',
                'userName' => 'required|string',
                'email' => 'nullable|string',
                'address' => 'nullable|string',
            ]);

            Log::info('Transaction validation passed.', ['validated_data' => $validated]);

            // Create a customer
            $customer = Customer::create([
                'name' => $validated['userName'],
                'phoneNumber' => $validated['phoneNumber'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'restaurantId' => $validated['restaurantId'],
            ]);

            // Create the order
            $order = Order::create([
                'tableNumber' => $validated['tableNumber'],
                'restaurantId' => $validated['restaurantId'],
                'user_id' => $customer->id,
                'orderDetails' => $validated['orderDetails'],
                'status' => 'processing',
            ]);

            DB::commit();

            // Send Notification to restaurant admin
            $user = UserProfile::where('restaurantId', $validated['restaurantId'])->first();

            if ($user && !empty($user->fcm)) {
                $this->sendNotification(
                    $user->fcm,
                    'New Order Received',
                    'Order #' . $order->id . ' has been placed.',
                    ['order_id' => $order->id]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully.',
                'data' => [
                    'customer' => $customer,
                    'order' => $order,
                ],
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e,
            ], 400);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating transaction.', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
            ], 500);
        }
    }

    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(storage_path(config('services.firebase.credentials')));

            $messaging = $factory->createMessaging();

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $messaging->send($message);
        } catch (Exception $e) {
            Log::error('Firebase Notification Failed', ['error' => $e->getMessage()]);
        }
    }



    /**
     * Get all categories.
     *
     * @OA\Get(
     *     path="/webMenu/categories",
     *     summary="Retrieve all categories by restaurant",
     *     description="Fetch a list of all categories based on the restaurant ID.",
     *     operationId="getAllCategories",
     *     tags={"WebAppMenu"},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         required=true,
     *         description="ID of the restaurant to fetch categories for",
     *         @OA\Schema(type="string", example="R1728231298")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="Category ID", example=1),
     *                     @OA\Property(property="categoryName", type="string", description="Category name", example="Pizza"),
     *                     @OA\Property(property="categoryImage", type="string", description="Full URL of the category image", example="http://example.com/images/pizza.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp", example="2024-11-26T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp", example="2024-11-26T10:00:00Z"),
     *                     @OA\Property(property="restaurantId", type="string", description="Restaurant ID", example="R1728231298")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No categories found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No categories found for the given restaurant ID.")
     *         )
     *     )
     * )
     */

    public function getAllCategories(Request $request)
    {
        // Validate restaurantId
        $validatedData = $request->validate([
            'restaurantId' => 'string|required'
        ]);

        // Fetch categories based on restaurantId
        $categories = Category::where('restaurantId', $validatedData['restaurantId'])->get();

        // Initialize an empty array for the response data
        $data = [];

        // Construct image URL for each category and prepare the response
        foreach ($categories as $category) {
            $imageUrl = env('APP_URL') . '/' . $category->categoryImage; // Construct image URL

            $data[] = [
                'id' => $category->id,
                'categoryName' => $category->categoryName,
                'categoryImage' => $imageUrl, // Send full image URL
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'restaurantId' => $category->restaurantId
            ];
        }

        // Return the response in JSON format
        return response()->json($data);
    }

    /**
     * Search menu items by category.
     *
     * @OA\Get(
     *     path="/menu/category/{id}",
     *     summary="Search menu by category",
     *     description="Retrieve all menu items based on the provided category ID.",
     *     operationId="searchMenuByCategory",
     *     tags={"WebAppMenu"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to filter menu items",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Menu item ID", example=101),
     *                 @OA\Property(property="itemName", type="string", description="Menu item name", example="Margherita Pizza"),
     *                 @OA\Property(property="itemImage", type="string", description="URL of the menu item image", example="http://example.com/images/pizza.jpg"),
     *                 @OA\Property(property="price", type="number", format="float", description="Price of the menu item", example=8.99),
     *                 @OA\Property(property="category", type="string", description="Category name", example="Pizza"),
     *                 @OA\Property(
     *                     property="ingredients",
     *                     type="array",
     *                     description="List of ingredients for the menu item",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="ingredientName", type="string", description="Ingredient name", example="Cheese"),
     *                         @OA\Property(property="quantity", type="number", format="float", description="Ingredient quantity", example=1.5),
     *                         @OA\Property(property="unit", type="string", description="Unit of the ingredient quantity", example="kg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No menu found for the given category ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No menu found for the given restaurant ID.")
     *         )
     *     )
     * )
     */


    public function searchMenuByCategory($id)
    {
        // Fetch all menu items for the given restaurant
        $menuItems = Menu::where('categoryId', $id)->get();

        if ($menuItems->isEmpty()) {
            return response()->json(['message' => 'No menu found for the given restaurant ID'], 404);
        }

        // Pre-fetch all categories and ingredients to optimize queries
        $categoryIds = $menuItems->pluck('categoryId')->unique();
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $menuIds = $menuItems->pluck('id')->unique();
        $menuInventories = MenuInventory::whereIn('menuId', $menuIds)->get();
        $stockIds = $menuInventories->pluck('stockId')->unique();
        $inventories = Inventory::whereIn('id', $stockIds)->get()->keyBy('id');

        $data = [];

        foreach ($menuItems as $menu) {
            $category = $categories->get($menu->categoryId);

            // Map ingredients for the current menu item
            $ingredients = $menuInventories->where('menuId', $menu->id)->map(function ($inventory) use ($inventories) {
                $ingredient = $inventories->get($inventory->stockId);
                return $ingredient ? [
                    'ingredientName' => $ingredient->itemName,
                    'quantity' => $inventory->quantity,
                    'unit' => $ingredient->unit,
                ] : null;
            })->filter()->values(); // Remove null and reindex

            // Add each menu item to the response data
            $data[] = [
                'id' => $menu->id,
                'itemName' => $menu->itemName,
                'itemImage' => $menu->itemImage,
                'price' => floatval($menu->price),
                'category' => $category ? $category->categoryName : null,
                'ingredients' => $ingredients,
            ];
        }

        // Return data as JSON response
        return response()->json($data);
    }
}
