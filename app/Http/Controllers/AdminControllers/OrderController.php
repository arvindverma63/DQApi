<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Menu;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Get all orders by restaurant ID",
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         description="The restaurant ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved orders"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No orders found for this restaurant"
     *     )
     * )
     */
    public function index(Request $request)
{
    // Validate restaurantId from the request
    $validatedData = $request->validate([
        'restaurantId' => 'required|string',
    ]);

    // Fetch orders by restaurantId
    $orders = Order::where('restaurantId', $validatedData['restaurantId'])->get();

    // Check if orders exist for the restaurant
    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found for this restaurant'], 404);
    }

    // Enhance orders with user and item details
    $enhancedOrders = $orders->map(function ($order) {
        // Fetch user profile details
        $userDetails = UserProfile::where('userId', $order->user_id)->first();

        // Decode the order details JSON
        $orderDetails = json_decode($order->orderDetails, true);

        // Initialize total
        $total = 0;

        // Fetch item details for each item in the order
        $itemDetails = collect($orderDetails)->map(function ($item) use (&$total) {
            $menuItem = Menu::where('id', $item['item_id'])->first();

            if ($menuItem) {
                // Calculate the total (price * quantity)
                $itemTotal = $menuItem->price * $item['quantity'];
                $total += $itemTotal;

                return [
                    'item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'price' => $menuItem->price,
                    'quantity' => $item['quantity'],
                    'item_total' => $itemTotal,
                ];
            }

            return null;  // If item not found
        })->filter();  // Remove null values (items not found)

        // Append user and item details to the order
        return [
            'order_id' => $order->id,
            'table_number' => $order->tableNumber,
            'restaurant_id' => $order->restaurantId,
            'status' => $order->status,
            'order_details' => $itemDetails->values()->toArray(),  // Get the item details
            'user' => $userDetails ? $userDetails->toArray() : null, // If userDetails found
            'total' => $total,  // Calculated total
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    });

    // Return the enhanced orders with user and item details
    return response()->json(['data' => $enhancedOrders, 'message' => 'Successfully retrieved orders'], 200);
}



    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "orderDetails", "user_id"},
     *             @OA\Property(property="tableNumber", type="string", example="12"),
     *             @OA\Property(property="restaurantId", type="string", example="ABC123"),
     *             @OA\Property(property="user_id", type="integer", example="1"),
     *             @OA\Property(property="orderDetails", type="object",
     *                 @OA\Property(property="item1", type="object",
     *                     @OA\Property(property="item_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     ),
     *                 @OA\Property(property="item2", type="object",
     *                     @OA\Property(property="item_id", type="integer", example=2),
     *                     @OA\Property(property="quantity", type="integer", example=1),
     *                )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tableNumber' => 'nullable|string',
            'restaurantId' => 'required|string',
            'user_id' => 'required|integer',  // Added user_id as required
            'orderDetails' => 'required|array', // Order details must be a JSON object (array)
        ]);

        // Store the new order
        $order = Order::create([
            'tableNumber' => $validatedData['tableNumber'],
            'restaurantId' => $validatedData['restaurantId'],
            'user_id' => $validatedData['user_id'],  // Saving user_id with order
            'orderDetails' => json_encode($validatedData['orderDetails']),
            'status' => 'processing', // Default status is 'processing'
        ]);

        return response()->json($order, 201);
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get a specific order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved order"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order);
    }

    /**
     * @OA\Put(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Update an existing order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "orderDetails", "user_id"},
     *             @OA\Property(property="tableNumber", type="string", example="12"),
     *             @OA\Property(property="restaurantId", type="string", example="ABC123"),
     *             @OA\Property(property="user_id", type="integer", example="1"),
     *             @OA\Property(property="orderDetails", type="object",
     *                 @OA\Property(property="item1", type="object",
     *                     @OA\Property(property="item_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="price", type="number", format="float", example=10.99)
     *                 ),
     *                 @OA\Property(property="item2", type="object",
     *                     @OA\Property(property="item_id", type="integer", example=2),
     *                     @OA\Property(property="quantity", type="integer", example=1),
     *                     @OA\Property(property="price", type="number", format="float", example=4.99)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'tableNumber' => 'nullable|string',
            'restaurantId' => 'required|string',
            'user_id' => 'required|integer', // Ensure user_id is validated
            'orderDetails' => 'required|array',
        ]);

        // Find the order and update it
        $order = Order::findOrFail($id);

        $order->update([
            'tableNumber' => $validatedData['tableNumber'],
            'restaurantId' => $validatedData['restaurantId'],
            'user_id' => $validatedData['user_id'], // Updating user_id
            'orderDetails' => json_encode($validatedData['orderDetails']),
        ]);

        return response()->json($order);
    }

    /**
     * @OA\Delete(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    /**
     * @OA\Put(
     *     path="/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     description="Update the status of an order to one of the following: 'processing', 'accept', 'reject', or 'complete'. When status is 'complete', stock will be decreased.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="complete", enum={"processing", "accept", "reject", "complete"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully and stock adjusted"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,accept,reject,complete'
        ]);

        $order = Order::find($id);

        // If the status is being changed to complete, decrease stock
        if ($request->status === 'complete') {
            $this->decreaseStock($order);
        }

        $order->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ], 200);
    }

    /**
     * Decrease stock based on order items.
     *
     * @param \App\Models\Order $order
     */
    protected function decreaseStock(Order $order)
    {
        foreach (json_decode($order->orderDetails, true) as $item) {
            // Using item_id to find the menu item
            $menuItem = Menu::find($item['item_id']);

            if ($menuItem && $menuItem->stock >= $item['quantity']) {
                $menuItem->stock -= $item['quantity'];
                $menuItem->save();
            } else {
                // Handle insufficient stock case
                return response()->json([
                    'message' => 'Insufficient stock for item ID: ' . $menuItem->id
                ], 400);
            }
        }
    }
}
