<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Log;

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
        // Validate restaurantId
        $validatedData = $request->validate([
            'restaurantId' => 'required|string',
        ]);

        // Fetch orders with joined customer data
        $orders = Order::select([
            'orders.id as order_id',
            'orders.tableNumber as table_number',
            'orders.restaurantId as restaurant_id',
            'orders.status',
            'orders.orderDetails as order_details',
            'orders.created_at',
            'orders.updated_at',
            'customers.id as customer_id',
            'customers.name',
            'customers.phoneNumber',
            'customers.email',
            'customers.address'
        ])
            ->leftJoin('customers', 'orders.user_id', '=', 'customers.id')
            ->where('orders.restaurantId', $validatedData['restaurantId'])
            ->where('orders.tableNumber', '!=', 'Delivery')
            ->get();

        // Process orders using orderDetails JSON
        $enhancedOrders = $orders->map(function ($order) {
            // Decode and validate order_details
            $rawDetails = $order->order_details;
            $decodedDetails = json_decode($rawDetails, true);

            // Handle double-encoded JSON
            if (is_string($decodedDetails)) {
                \Log::warning('Double-encoded order_details detected for order_id: ' . $order->order_id, [
                    'raw_details' => $rawDetails,
                    'decoded_details' => $decodedDetails
                ]);
                // Try decoding again
                $decodedDetails = json_decode($decodedDetails, true);
            }

            // Debug JSON decoding issues
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('JSON decode error for order_id: ' . $order->order_id, [
                    'raw_details' => $rawDetails,
                    'error' => json_last_error_msg()
                ]);
                return [
                    'order_id' => $order->order_id,
                    'table_number' => $order->table_number,
                    'restaurant_id' => $order->restaurant_id,
                    'status' => $order->status,
                    'order_details' => [],
                    'user' => $order->customer_id ? [
                        'id' => $order->customer_id,
                        'name' => $order->name,
                        'phoneNumber' => $order->phoneNumber,
                        'email' => $order->email,
                        'address' => $order->address
                    ] : null,
                    'total' => 0,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            }

            // Ensure decoded details is an array
            if (!is_array($decodedDetails)) {
                \Log::error('Decoded order_details is not an array for order_id: ' . $order->order_id, [
                    'raw_details' => $rawDetails,
                    'decoded_details' => $decodedDetails
                ]);
                return [
                    'order_id' => $order->order_id,
                    'table_number' => $order->table_number,
                    'restaurant_id' => $order->restaurant_id,
                    'status' => $order->status,
                    'order_details' => [],
                    'user' => $order->customer_id ? [
                        'id' => $order->customer_id,
                        'name' => $order->name,
                        'phoneNumber' => $order->phoneNumber,
                        'email' => $order->email,
                        'address' => $order->address
                    ] : null,
                    'total' => 0,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            }

            $orderDetails = collect($decodedDetails);
            // Log orderDetails for debugging
            \Log::debug('Processing order_details for order_id: ' . $order->order_id, [
                'order_details' => $decodedDetails
            ]);

            // Calculate item details and total using JSON data
            $total = 0;
            $itemDetails = $orderDetails->map(function ($item) use (&$total, $order) {
                // Validate item structure
                if (!isset($item['id']) || !isset($item['itemName']) || !isset($item['price']) || !isset($item['quantity'])) {
                    \Log::warning('Invalid item structure in order_details for order_id: ' . $order->order_id, [
                        'item' => $item
                    ]);
                    return null;
                }

                $itemTotal = $item['price'] * $item['quantity'];
                $total += $itemTotal;

                return [
                    'item_id' => (string) $item['id'],
                    'item_name' => $item['itemName'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'item_total' => $itemTotal,
                ];
            })->filter()->values()->toArray();

            return [
                'order_id' => $order->order_id,
                'table_number' => $order->table_number,
                'restaurant_id' => $order->restaurant_id,
                'status' => $order->status,
                'order_details' => $itemDetails,
                'user' => $order->customer_id ? [
                    'id' => $order->customer_id,
                    'name' => $order->name,
                    'phoneNumber' => $order->phoneNumber,
                    'email' => $order->email,
                    'address' => $order->address
                ] : null,
                'total' => $total,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return response()->json([
            'data' => $enhancedOrders,
            'message' => 'Successfully retrieved orders'
        ], 200);
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
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="deliver_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="orderDetails",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "itemName", "category", "price", "ingredients", "imageUrl", "quantity"},
     *                     @OA\Property(property="id", type="string", example="204"),
     *                     @OA\Property(property="itemName", type="string", example="Tandoori Momos"),
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
            'deliver_id' => 'nullable|integer',
        ]);

        // Store the new order
        $order = Order::create([
            'tableNumber' => $validatedData['tableNumber'],
            'restaurantId' => $validatedData['restaurantId'],
            'user_id' => $validatedData['user_id'],  // Saving user_id with order
            'orderDetails' => json_encode($validatedData['orderDetails']),
            'status' => 'processing', // Default status is 'processing'
            'deliver_id' => $validatedData['deliver_id']
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
            'type' => 'nullable',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        if ($request->status === 'reject') {
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
                'type' => $request->type ?? "web order", // Replace with actual payment type if available
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
     *     path="/orders/notification/{id}",
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

    /**
     * @OA\Get(
     *     path="/getOrderByDelivery",
     *     tags={"Orders"},
     *     summary="Get paginated orders by restaurant ID",
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         description="The ID of the restaurant",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved orders",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="order_id", type="integer"),
     *                 @OA\Property(property="table_number", type="integer"),
     *                 @OA\Property(property="restaurant_id", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="order_details", type="array", @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="item_id", type="integer"),
     *                     @OA\Property(property="item_name", type="string"),
     *                     @OA\Property(property="price", type="number", format="float"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="item_total", type="number", format="float")
     *                 )),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="phoneNumber", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="address", type="string")
     *                 ),
     *                 @OA\Property(property="total", type="number", format="float"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true)
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No orders found for this restaurant"
     *     )
     * )
     */

    public function getOrderByDelivery(Request $request)
    {
        $validatedData = $request->validate([
            'restaurantId' => 'required|string',
        ]);

        // Log input
        Log::info('Fetching delivery orders', ['restaurantId' => $validatedData['restaurantId']]);

        // Fetch orders without aliasing orderDetails
        $orders = Order::select([
            'orders.id as order_id',
            'orders.tableNumber as table_number',
            'orders.restaurantId as restaurant_id',
            'orders.status',
            'orders.orderDetails',
            'orders.created_at',
            'orders.updated_at',
            'customers.id as customer_id',
            'customers.name',
            'customers.phoneNumber',
            'customers.email',
            'customers.address'
        ])
            ->leftJoin('customers', 'orders.user_id', '=', 'customers.id')
            ->where('orders.restaurantId', $validatedData['restaurantId'])
            ->where('orders.tableNumber', 'Delivery')
            ->orderBy('orders.id', 'desc') // <--- This line adds the descending order
            ->paginate();

        // Log raw orders
        Log::info('Raw orders fetched', ['count' => $orders->count(), 'data' => $orders->toArray()]);

        // Process orders
        $enhancedOrders = $orders->map(function ($order) {
            // Log raw orderDetails
            Log::info('Processing order', [
                'order_id' => $order->order_id,
                'raw_orderDetails' => $order->orderDetails,
                'is_null' => is_null($order->orderDetails),
                'type' => gettype($order->orderDetails)
            ]);

            // Access orderDetails directly
            $rawDetails = $order->orderDetails ?? '[]';
            $decodedDetails = is_array($rawDetails)
                ? $rawDetails
                : json_decode($rawDetails, true);

            // Log decoding result
            Log::info('Decoded orderDetails', [
                'order_id' => $order->order_id,
                'decoded' => $decodedDetails,
                'json_error' => json_last_error_msg()
            ]);

            // Handle JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedDetails)) {
                Log::error('Invalid orderDetails for order_id: ' . $order->order_id, [
                    'raw' => $rawDetails,
                    'decoded' => $decodedDetails,
                    'error' => json_last_error_msg()
                ]);
                $decodedDetails = [];
            }

            $orderDetails = collect($decodedDetails);
            $total = 0;
            $itemDetails = $orderDetails->map(function ($item) use (&$total, $order) {
                if (
                    !isset($item['id']) ||
                    !isset($item['itemName']) ||
                    !isset($item['price']) ||
                    !isset($item['quantity'])
                ) {
                    Log::warning('Invalid item structure for order_id: ' . $order->order_id, ['item' => $item]);
                    return null;
                }

                $itemTotal = $item['price'] * $item['quantity'];
                $total += $itemTotal;

                return [
                    'item_id' => $item['id'],
                    'item_name' => $item['itemName'],
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'item_total' => $itemTotal,
                ];
            })->filter()->values()->toArray();

            return [
                'order_id' => $order->order_id,
                'table_number' => $order->table_number,
                'restaurant_id' => $order->restaurant_id,
                'status' => $order->status,
                'order_details' => $itemDetails,
                'user' => $order->customer_id ? [
                    'id' => $order->customer_id,
                    'name' => $order->name,
                    'phoneNumber' => $order->phoneNumber,
                    'email' => $order->email,
                    'address' => $order->address
                ] : [],
                'total' => round($total, 2),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return response()->json([
            'data' => $enhancedOrders,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'next_page_url' => $orders->nextPageUrl(),
                'prev_page_url' => $orders->previousPageUrl(),
            ],
            'message' => 'Successfully retrieved orders'
        ], 200);
    }
}
