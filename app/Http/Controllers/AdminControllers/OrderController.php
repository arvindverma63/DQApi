<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Transaction;
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


        // Preload customers and menu items for optimization
        $customerIds = $orders->pluck('user_id')->unique();
        $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');

        $menuItemIds = $orders->flatMap(function ($order) {
            return collect(json_decode($order->orderDetails, true))->pluck('id');
        })->unique();
        $menuItems = Menu::whereIn('id', $menuItemIds)->get()->keyBy('id');

        // Map orders with user and item details
        $enhancedOrders = $orders->map(function ($order) use ($customers, $menuItems) {
            $userDetails = $customers->get($order->user_id);
            $orderDetails = collect(json_decode($order->orderDetails, true));

            $total = 0;

            // Map item details and calculate totals
            $itemDetails = $orderDetails->map(function ($item) use ($menuItems, &$total) {
                $menuItem = $menuItems->get($item['id']);
                if ($menuItem) {
                    $itemTotal = $menuItem->price * $item['quantity'];
                    $total += $itemTotal;

                    return [
                        'item_id' => $menuItem->id,
                        'item_name' => $menuItem->itemName,
                        'price' => $menuItem->price,
                        'quantity' => $item['quantity'],
                        'item_total' => $itemTotal,
                    ];
                }
                return null;
            })->filter(); // Remove null values

            return [
                'order_id' => $order->id,
                'table_number' => $order->tableNumber,
                'restaurant_id' => $order->restaurantId,
                'status' => $order->status,
                'order_details' => $itemDetails->values()->toArray(),
                'user' => $userDetails ? $userDetails->only(['id', 'name', 'phoneNumber', 'email', 'address']) : null,
                'total' => $total,
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
     *             @OA\Property(property="status", type="string", example="complete", enum={"processing", "accept", "reject", "complete"}),
     *             @OA\Property(property="type", type="string", example="Onlilne", enum={"online", "offline"})
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
            'status' => 'required|in:processing,accept,reject,complete',
            'type'=> 'nullable',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        if($request->status === 'reject'){
            $order->update([
                'status' => $request->status,
            ]);
        }

        // If the status is being changed to complete
        if ($request->status === 'complete') {
            $orderDetails = json_decode($order->orderDetails, true);

            // Calculate totals
            $subTotal = collect($orderDetails)->sum(function ($item) {
                return floatval($item['price']) * intval($item['quantity']);
            });

            $tax = 0; // Example: 10% tax
            $discount = 0; // Adjust discount logic if applicable
            $total = $subTotal + $tax - $discount;

            // Prepare transaction data
            $transactionData = [
                'user_id' => $order->user_id,
                'items' => collect($orderDetails)->map(function ($item) {
                    return [
                        'itemId' => $item['id'], // Ensure the key matches your database structure
                        'itemName' => $item['item_name'] ?? $item['itemName'],
                        'price' => floatval($item['price']),
                        'quantity' => intval($item['quantity']),
                    ];
                })->toArray(),
                'tax' => $tax,
                'discount' => $discount,
                'sub_total' => $subTotal,
                'total' => $total,
                'type' => $request['type'], // Replace with actual payment type if available
                'restaurantId' => $order->restaurantId,
                'tableNumber' => $order->tableNumber,
            ];

            // Call TransactionController to add transaction
            $transactionController = app(TransactionController::class);
            $transactionResponse = $transactionController->addTransaction(new Request($transactionData));
            // Update the order status
            $order->update([
                'status' => $request->status,
            ]);

            if ($transactionResponse->getStatusCode() !== 201) {
                return response()->json([
                    'message' => 'Failed to create transaction.',
                    'error' => $transactionResponse->getData(),
                ], $transactionResponse->getStatusCode());
            }
        }



        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/notification/{id}",
     *     summary="Retrieve orders with pending notifications for a specific restaurant",
     *     description="Fetches all orders where the notification status is 0 and matches the given restaurant ID.",
     *     operationId="getPendingNotificationsByRestaurant",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the restaurant to filter orders by",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Order ID"),
     *                 @OA\Property(property="restaurantId", type="integer", description="ID of the restaurant"),
     *                 @OA\Property(property="notification", type="integer", description="Notification status (0 or 1)"),
     *                 @OA\Property(property="other_field", type="string", description="Other fields in the order model")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant not found or no orders with pending notifications"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getNotification($id)
    {
        $response = Order::where('restaurantId', $id)->where('notification', 0)->get();
        return response()->json($response);
    }

    /**
     * @OA\Put(
     *     path="/orders/status/notification/{id}",
     *     summary="Update notification status for an order",
     *     description="Sets the notification status to 1 for the specified order, indicating that the notification has been sent.",
     *     operationId="updateNotificationStatus",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         description="ID of the restaurant associated with the order",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example=101
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification status updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to update notification status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Failed to update notification status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function updateNotificationStatus(Request $request, $id)
    {
        // Validate the restaurantId and id
        $validatedData = $request->validate([
            'restaurantId' => 'required|string',
        ]);

        // Update the notification status to 1 for the given order ID
        $response = Order::where('restaurantId', $request->input('restaurantId'))
            ->where('id', $id)
            ->update(['notification' => 1]);

        // Check if the update was successful
        if ($response) {
            return response()->json([
                'message' => 'Notification status updated successfully'
            ], 200); // HTTP Status Code 200: OK
        } else {
            return response()->json([
                'message' => 'Failed to update notification status'
            ], 400); // HTTP Status Code 400: Bad Request
        }
    }
}
