<?php

namespace App\Http\Controllers\WebAppControllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\MenuInventory;
use App\Models\Order;
use App\Models\Transaction;
use DB;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Log;

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
        $menuItems = Menu::where('restaurantId', $request['restaurantId'])->get();

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
     *             @OA\Property(
     *                 property="orderDetails",
     *                 type="array",
     *                 description="List of items in the transaction",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="item_id", type="integer", description="Item ID", example=101),
     *                     @OA\Property(property="name", type="string", description="Item name", example="Pizza"),
     *                     @OA\Property(property="price", type="number", format="float", description="Item price", example=200.50),
     *                     @OA\Property(property="quantity", type="integer", description="Item quantity", example=2)
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
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'tableNumber' => 'nullable|string',
                'restaurantId' => 'required|string',
                'orderDetails' => 'required|array', // Order details must be a JSON object (array)
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

            if ($customer) {
                // Create the order
                $order = Order::create([
                    'tableNumber' => $validated['tableNumber'],
                    'restaurantId' => $validated['restaurantId'],
                    'user_id' => $customer->id,  // Link the order to the customer
                    'orderDetails' => $validated['orderDetails'], // Correct key used
                    'status' => 'processing', // Default status
                ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e,
            ], 400);
        } catch (Exception $e) {
            // Rollback in case of error
            DB::rollBack();

            Log::error('Error creating transaction.', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
            ], 500);
        }
    }


    /**
     * Get all categories.
     *
     * @OA\Get(
     *     path="/webMenu/categories",
     *     summary="Retrieve all categories",
     *     description="Fetch a list of all categories.",
     *     operationId="getAllCategories",
     *     tags={"WebAppMenu"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Category ID", example=1),
     *                 @OA\Property(property="name", type="string", description="Category name", example="Pizza"),
     *                 @OA\Property(property="description", type="string", description="Category description", example="Italian cuisine pizzas"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp", example="2024-11-26T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp", example="2024-11-26T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No categories found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No categories found.")
     *         )
     *     )
     * )
     */
    public function getAllCategories()
    {
        $response = Category::all();

        if ($response->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No categories found.'
            ], 404);
        }

        return response()->json($response, 200);
    }
}
