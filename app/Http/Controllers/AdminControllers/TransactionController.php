<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\MenuInventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     required={"user_id", "items", "tax", "discount", "sub_total", "total", "payment_type", "restaurantId"},
 *     @OA\Property(property="id", type="integer", description="Transaction ID", example=1),
 *     @OA\Property(property="user_id", type="integer", description="User ID", example=1),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="List of items in the transaction",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="item_id", type="integer", description="Item ID", example=101),
 *             @OA\Property(property="name", type="string", description="Item name", example="Item 1"),
 *             @OA\Property(property="price", type="number", format="float", description="Item price", example=100.50),
 *             @OA\Property(property="quantity", type="integer", description="Item quantity", example=2)
 *         )
 *     ),
 *     @OA\Property(property="tax", type="number", format="float", description="Tax amount", example=15.00),
 *     @OA\Property(property="discount", type="number", format="float", description="Discount amount", example=10.00),
 *     @OA\Property(property="sub_total", type="number", format="float", description="Subtotal amount", example=300.00),
 *     @OA\Property(property="total", type="number", format="float", description="Total amount", example=305.00),
 *     @OA\Property(property="payment_type", type="string", description="Payment type", example="credit_card"),
 *     @OA\Property(property="restaurantId", type="string", description="Restaurant ID", example="R1728231298"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Transaction creation timestamp", example="2024-11-11T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Transaction update timestamp", example="2024-11-11T12:00:00.000000Z")
 * )
 */

class TransactionController extends Controller
{
    /**
     * Add a new transaction.
     *
     * @OA\Post(
     *     path="/transactions",
     *     summary="Add a new transaction",
     *     description="Create a new transaction with items, tax, discount, and restaurant ID.",
     *     operationId="addTransaction",
     *     tags={"Transaction"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", description="User ID", example=1),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="List of items in the transaction",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="item_id", type="integer", description="Item ID", example=101),
     *                     @OA\Property(property="name", type="string", description="Item name", example="Item 1"),
     *                     @OA\Property(property="price", type="number", format="float", description="Item price", example=100.50),
     *                     @OA\Property(property="quantity", type="integer", description="Item quantity", example=2)
     *                 )
     *             ),
     *             @OA\Property(property="tax", type="number", format="float", description="Tax amount", example=15.00),
     *             @OA\Property(property="discount", type="number", format="float", description="Discount amount", example=10.00),
     *             @OA\Property(property="sub_total", type="number", format="float", description="Subtotal amount", example=300.00),
     *             @OA\Property(property="total", type="number", format="float", description="Total amount", example=305.00),
     *             @OA\Property(property="type", type="string", description="Payment type", example="credit_card"),
     *             @OA\Property(property="restaurantId", type="string", description="Restaurant ID", example="R1728231298")
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
     *     )
     * )
     */

    public function addTransaction(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'items' => 'required', // Ensure this is a valid JSON string or array
                'tax' => 'required|numeric|min:0',
                'discount' => 'required|numeric|min:0',
                'sub_total' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'type' => 'required|string',
                'restaurantId' => 'required|string',
            ]);

            Log::info('Transaction validation passed.', ['validated_data' => $validated]);

            // Create the transaction
            $transaction = Transaction::create([
                'user_id' => $validated['user_id'],
                'items' => is_array($validated['items']) ? json_encode($validated['items']) : $validated['items'], // Store items as JSON
                'tax' => $validated['tax'],
                'discount' => $validated['discount'],
                'sub_total' => $validated['sub_total'],
                'total' => $validated['total'],
                'payment_type' => $validated['type'],
                'restaurantId' => $validated['restaurantId'],
            ]);

            Log::info('Transaction created successfully.', ['transaction_id' => $transaction->id]);

            // Decode items if necessary
            $jsonData = is_array($validated['items']) ? $validated['items'] : json_decode($validated['items'], true);
            Log::info('Decoded items JSON.', ['items' => $jsonData]);

            foreach ($jsonData as $data) {
                // Fetch the menu
                $menu = Menu::where('id', $data['itemId'])->first();
                if (!$menu) {
                    Log::error('Menu item not found.', ['item_id' => $data['itemId']]);
                    return response()->json([
                        'success' => false,
                        'message' => "Menu item with ID {$data['itemId']} not found.",
                    ], 404);
                }

                Log::info('Menu item fetched.', ['menu_id' => $menu->id]);

                // Fetch the menu inventory
                $inventory = MenuInventory::where('menuId', $menu->id)->first();
                if (!$inventory) {
                    Log::error('Menu inventory not found.', ['menu_id' => $menu->id]);
                    return response()->json([
                        'success' => false,
                        'message' => "Inventory for menu ID {$menu->id} not found.",
                    ], 404);
                }

                Log::info('Menu inventory fetched.', ['inventory_id' => $inventory->id]);

                // Calculate quantity
                $requiredQuantity = $data['quantity'];

                // Fetch the stock
                $stock = Inventory::where('id', $inventory->stockId)->first();
                if (!$stock) {
                    Log::error('Stock not found.', ['stock_id' => $inventory->stockId]);
                    return response()->json([
                        'success' => false,
                        'message' => "Stock with ID {$inventory->stockId} not found.",
                    ], 404);
                }

                Log::info('Stock fetched.', ['stock_id' => $stock->id]);

                // Check and update stock
                if ($stock->quantity < $requiredQuantity) {
                    Log::warning('Not enough stock.', [
                        'stock_id' => $stock->id,
                        'available_quantity' => $stock->quantity,
                        'required_quantity' => $requiredQuantity,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough stock for item ID {$data['itemId']}.",
                    ], 400);
                }

                $stock->quantity -= $requiredQuantity;
                $stock->save();

                Log::info('Stock quantity updated.', [
                    'stock_id' => $stock->id,
                    'new_quantity' => $stock->quantity,
                ]);
            }

            Log::info('Transaction completed successfully.', ['transaction_id' => $transaction->id]);

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Transaction created successfully.',
            ], 201);
        } catch (\Exception $e) {
            Log::error('An error occurred while adding a transaction.', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/transactions/{id}",
     *     summary="Get transactions by restaurant ID",
     *     description="Retrieve a list of transactions for a specific restaurant.",
     *     operationId="getTransaction",
     *     tags={"Transaction"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(type="string", example="R1728231298")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of transactions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No transactions found for the given restaurant ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No transactions found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions.")
     *         )
     *     )
     * )
     */
    public function getTransaction($id)
    {
        try {
            // Fetch transactions for the specified restaurant ID
            $transactions = Transaction::where('restaurantId', $id)->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No transactions found.'
                ], 404);
            }

            // Transform the data to match the desired structure
            $responseData = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'items' => $transaction->items, // Decode JSON to array
                    'tax' => $transaction->tax,
                    'discount' => $transaction->discount,
                    'sub_total' => $transaction->sub_total,
                    'total' => $transaction->total,
                    'payment_type' => $transaction->payment_type,
                    'restaurantId' => $transaction->restaurantId,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ];
            });

            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving transactions.'
            ], 500);
        }
    }

    /**
 * @OA\Get(
 *     path="/transactionById/{id}",
 *     summary="Get transaction details by ID",
 *     description="Fetches transaction details for the specified transaction ID.",
 *     operationId="getTransactionById",
 *     tags={"Transaction"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the transaction to fetch",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction details retrieved successfully",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", description="Transaction ID"),
 *                 @OA\Property(property="userName", type="string", description="Customer's name"),
 *                 @OA\Property(property="items", type="array", @OA\Items(type="string"), description="List of items in the transaction"),
 *                 @OA\Property(property="tax", type="number", format="float", description="Tax applied to the transaction"),
 *                 @OA\Property(property="discount", type="number", format="float", description="Discount applied to the transaction"),
 *                 @OA\Property(property="sub_total", type="number", format="float", description="Subtotal before tax and discount"),
 *                 @OA\Property(property="total", type="number", format="float", description="Total after tax and discount"),
 *                 @OA\Property(property="payment_type", type="string", description="Payment method used (e.g., credit card, cash)"),
 *                 @OA\Property(property="restaurantId", type="integer", description="Restaurant ID associated with the transaction"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", description="Transaction creation date and time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Transaction last update date and time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example="false"),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example="false"),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions.")
 *         )
 *     )
 * )
 */
public function getTransactionById($id)
{
    try {
        // Fetch transactions for the specified restaurant ID
        $transactions = Transaction::where('id', $id)->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        // Transform the data to match the desired structure
        $responseData = $transactions->map(function ($transaction) {

            $customer = Customer::find($transaction->user_id);
            return [
                'id' => $transaction->id,
                'userName' => $customer->name,
                'items' => json_decode($transaction->items), // Decode JSON to array
                'tax' => $transaction->tax,
                'discount' => $transaction->discount,
                'sub_total' => $transaction->sub_total,
                'total' => $transaction->total,
                'payment_type' => $transaction->payment_type,
                'restaurantId' => $transaction->restaurantId,
                'created_at' => $transaction->created_at->format('d:m:Y H:i:s'),
                'updated_at' => $transaction->updated_at,
            ];
        });

        return response()->json($responseData, 200);
    } catch (\Exception $e) {
        // Handle any unexpected errors
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.'
        ], 500);
    }
}

}
