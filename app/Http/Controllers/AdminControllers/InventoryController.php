<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

/**
 * @OA\Schema(
 *     schema="Inventory",
 *     type="object",
 *     title="Inventory",
 *     description="Inventory schema",
 *     @OA\Property(property="id", type="integer", example=1, description="Inventory ID"),
 *     @OA\Property(property="itemName", type="string", example="Item Name", description="Name of the inventory item"),
 *     @OA\Property(property="quantity", type="number", format="float", example="100.123", description="Quantity of the inventory item in decimal format (up to 3 decimal places)"),
 *     @OA\Property(property="unit", type="string", example="kg", description="Unit of the inventory item"),
 *     @OA\Property(property="supplierId", type="integer", example=1, description="Supplier ID related to the inventory item"),
 *     @OA\Property(property="restaurantId", type="string", example="R123456789", description="Restaurant ID related to the inventory item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-11T13:55:52.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-12T13:55:52.000000Z"),
 * )
 */

class InventoryController extends Controller
{
    /**
 * @OA\Get(
 *     path="/inventories",
 *     summary="Get all inventory items",
 *     description="Retrieve a list of all inventory items, including the supplier name",
 *     tags={"Inventory"},
 *     @OA\Parameter(
 *         name="restaurantId",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string"),
 *         description="Restaurant ID"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="A list of inventory items",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Inventory")
 *         )
 *     ),
 *     @OA\Response(response=404, description="No inventory items found")
 * )
 */
public function getAllInventory(Request $request)
{
    // Validate request to make sure restaurantId is provided
    $validator = Validator::make($request->all(), [
        'restaurantId' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Fetch inventory items filtered by restaurantId and include the related supplier details
    $restaurantId = $request->restaurantId;

    $inventoryItems = Inventory::where('restaurantId', $restaurantId)
        ->with('supplier:id,supplierName') // Eager load supplier and include only 'id' and 'supplierName' fields
        ->get();



    return response()->json(['data' => $inventoryItems, 'message' => 'Inventory items retrieved successfully'], 200);
}


    /**
     * @OA\Get(
     *     path="/inventories/{id}",
     *     summary="Get a specific inventory item",
     *     description="Retrieve a specific inventory item by ID",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Inventory item ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item details",
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     *     @OA\Response(response=404, description="Inventory item not found")
     * )
     */
    public function getInventory($id)
    {
        $inventoryItem = Inventory::find($id);

        if (!$inventoryItem) {
            return response()->json(['message' => 'Inventory item not found'], 404);
        }

        return response()->json(['data' => $inventoryItem, 'message' => 'Inventory item retrieved successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/inventories",
     *     summary="Create a new inventory item",
     *     description="Add a new item to the inventory",
     *     tags={"Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inventory item created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function createInventory(Request $request)
    {
        // Validate the request with decimal quantity (up to 3 decimal places)
        $validator = Validator::make($request->all(), [
            'itemName' => 'required|string|max:255',
            'quantity' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,3})?$/'], // Decimal with up to 3 decimal places
            'unit' => 'required|string|max:10',
            'supplierId' => 'required|exists:suppliers,id',
            'restaurantId' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create new inventory item
        $inventory = Inventory::create($request->all());

        return response()->json(['data' => $inventory, 'message' => 'Inventory item created successfully'], 201);
    }

    /**
     * @OA\Put(
     *     path="/inventories/{id}",
     *     summary="Update an inventory item",
     *     description="Update details of an inventory item",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Inventory item ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="Inventory item not found")
     * )
     */
    public function updateInventory(Request $request, $id)
    {
        // Validate the request with decimal quantity (up to 3 decimal places)
        $validator = Validator::make($request->all(), [
            'itemName' => 'string|max:255',
            'quantity' => 'numeric', // Decimal with up to 3 decimal places
            'unit' => 'string|max:10',
            'supplierId' => 'exists:suppliers,id',
            'restaurantId' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory item not found'], 404);
        }

        $inventory->update($request->all());

        return response()->json(['data' => $inventory, 'message' => 'Inventory item updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/inventories/{id}",
     *     summary="Delete an inventory item",
     *     description="Delete an inventory item by ID",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Inventory item ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item deleted successfully",
     *         @OA\JsonContent(type="string", example="Inventory item deleted successfully")
     *     ),
     *     @OA\Response(response=404, description="Inventory item not found")
     * )
     */
    public function deleteInventory($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory item not found'], 404);
        }

        $inventory->delete();

        return response()->json(['message' => 'Inventory item deleted successfully'], 200);
    }
}
