<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;
use App\Models\MenuInventory;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="MenuInventory",
 *     description="API Endpoints for managing menu inventory"
 * )
 */
/**
 * @OA\Schema(
 *     schema="MenuInventory",
 *     type="object",
 *     title="Menu Inventory",
 *     description="Menu Inventory schema",
 *     @OA\Property(property="id", type="integer", example=1, description="Menu Inventory ID"),
 *     @OA\Property(property="menuId", type="integer", example=5, description="ID of the menu item"),
 *     @OA\Property(property="restaurantId", type="string", example="R123456789", description="Restaurant ID associated with the inventory"),
 *     @OA\Property(property="quantity", type="number", format="float", example=2.5, description="Quantity of the menu item"),
 *     @OA\Property(property="stockId", type="integer", example=3, description="ID of the associated stock item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-11T13:55:52.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-12T13:55:52.000000Z"),
 * )
 */
class MenuInventoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/menu_inventory",
     *     summary="Get all menu inventory items",
     *     description="Retrieve a list of all menu inventory items",
     *     tags={"MenuInventory"},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Restaurant ID to filter inventory"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of menu inventory items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MenuInventory")
     *         )
     *     ),
     *     @OA\Response(response=404, description="No menu inventory items found")
     * )
     */
    public function getAllMenuInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurantId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $restaurantId = $request->restaurantId;
        $menuInventoryItems = MenuInventory::where('restaurantId', $restaurantId)->get();

        if ($menuInventoryItems->isEmpty()) {
            return response()->json(['message' => 'No menu inventory items found'], 404);
        }

        return response()->json(['data' => $menuInventoryItems, 'message' => 'Menu inventory items retrieved successfully'], 200);
    }

    /**
     * @OA\Get(
     *     path="/menu_inventory/{id}",
     *     summary="Get a specific menu inventory item",
     *     description="Retrieve a specific menu inventory item by ID",
     *     tags={"MenuInventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Menu Inventory ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu inventory item details",
     *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
     *     ),
     *     @OA\Response(response=404, description="Menu inventory item not found")
     * )
     */
    public function getMenuInventory($id)
    {
        $menuInventoryItem = MenuInventory::find($id);

        if (!$menuInventoryItem) {
            return response()->json(['message' => 'Menu inventory item not found'], 404);
        }

        return response()->json(['data' => $menuInventoryItem, 'message' => 'Menu inventory item retrieved successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/menu_inventory",
     *     summary="Create a new menu inventory item",
     *     description="Add a new item to the menu inventory",
     *     tags={"MenuInventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu inventory item created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function createMenuInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menuId' => 'required|integer',
            'restaurantId' => 'required|string',
            'quantity' => 'required|numeric|min:0.001',
            'stockId' => 'required|integer|exists:inventory,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $menuInventory = MenuInventory::create($request->all());

        return response()->json(['data' => $menuInventory, 'message' => 'Menu inventory item created successfully'], 201);
    }

    /**
 * @OA\Post(
 *     path="/menu_inventory/save",
 *     summary="Create or update a menu inventory item",
 *     description="Add a new item to the menu inventory or update an existing one",
 *     tags={"MenuInventory"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Menu inventory item created or updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
 *     ),
 *     @OA\Response(response=400, description="Invalid input")
 * )
 */
public function saveInventoryItem(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'nullable|integer|exists:menu_inventory,id',
        'menuId' => 'required|integer',
        'restaurantId' => 'required|string',
        'quantity' => 'required|numeric|min:0.001',
        'stockId' => 'required|integer|exists:inventories,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $menuInventory = MenuInventory::updateOrCreate(
        ['id' => $request->id],
        [
            'menuId' => $request->menuId,
            'restaurantId' => $request->restaurantId,
            'quantity' => $request->quantity,
            'stockId' => $request->stockId,
        ]
    );

    return response()->json([
        'data' => $menuInventory,
        'message' => $menuInventory->wasRecentlyCreated
            ? 'Menu inventory item created successfully'
            : 'Menu inventory item updated successfully'
    ], 201);
}


    /**
     * @OA\Put(
     *     path="/menu_inventory/{id}",
     *     summary="Update a menu inventory item",
     *     description="Update details of a menu inventory item",
     *     tags={"MenuInventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Menu Inventory ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu inventory item updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MenuInventory")
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="Menu inventory item not found")
     * )
     */
    public function updateMenuInventory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'menuId' => 'integer',
            'restaurantId' => 'string',
            'quantity' => 'numeric|min:0.001',
            'stockId' => 'integer|exists:inventory,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $menuInventory = MenuInventory::find($id);

        if (!$menuInventory) {
            return response()->json(['message' => 'Menu inventory item not found'], 404);
        }

        $menuInventory->update($request->all());

        return response()->json(['data' => $menuInventory, 'message' => 'Menu inventory item updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/menu_inventory/{id}",
     *     summary="Delete a menu inventory item",
     *     description="Delete a menu inventory item by ID",
     *     tags={"MenuInventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Menu Inventory ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu inventory item deleted successfully",
     *         @OA\JsonContent(type="string", example="Menu inventory item deleted successfully")
     *     ),
     *     @OA\Response(response=404, description="Menu inventory item not found")
     * )
     */
    public function deleteMenuInventory($id)
    {
        $menuInventory = MenuInventory::find($id);

        if (!$menuInventory) {
            return response()->json(['message' => 'Menu inventory item not found'], 404);
        }

        $menuInventory->delete();

        return response()->json(['message' => 'Menu inventory item deleted successfully'], 200);
    }
}
