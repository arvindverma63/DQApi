<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\MenuInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Menu",
 *     type="object",
 *     title="Menu",
 *     description="Menu Model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="itemName", type="string", example="Pizza"),
 *     @OA\Property(property="itemImage", type="string", example="/menus/pizza.jpg", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=9.99),
 *     @OA\Property(property="categoryId", type="integer", example=1),
 *     @OA\Property(property="restaurantId", type="string", example="1"),
 *     @OA\Property(property="stock", type="integer", example=100, nullable=true),
 *     @OA\Property(property="sub_category", type="integer", example=1, nullable=true),
 *     @OA\Property(property="status", type="integer", enum={0,1}, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class MenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/menu",
     *     summary="Get all menu items",
     *     tags={"Menu"},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu items retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="menus",
     *                     type="array",
     *                     @OA\Items(
     *                         allOf={
     *                             @OA\Schema(ref="#/components/schemas/Menu"),
     *                             @OA\Schema(
     *                                 @OA\Property(
     *                                     property="stockItems",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id", type="integer", example=1),
     *                                         @OA\Property(property="stockId", type="integer", example=1),
     *                                         @OA\Property(property="quantity", type="number", example=10.5),
     *                                         @OA\Property(property="name", type="string", example="Cheese")
     *                                     )
     *                                 )
     *                             )
     *                         }
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="inventoryOptions",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Inventory")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Menus and inventory options retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function index(Request $request)
    {
        $request->validate(['restaurantId' => 'required|string']);

        $menus = Menu::where('restaurantId', $request->restaurantId)->get();
        $stockItems = MenuInventory::whereIn('menuId', $menus->pluck('id'))->get();
        $inventoryItems = Inventory::whereIn('id', $stockItems->pluck('stockId'))->get()->keyBy('id');

        $menus->transform(function ($menu) use ($stockItems, $inventoryItems) {
            $menu->itemImage = $menu->itemImage ? url($menu->itemImage) : null;
            $menu->stockItems = $stockItems->where('menuId', $menu->id)->map(function ($stockItem) use ($inventoryItems) {
                return [
                    'id' => $stockItem->id,
                    'stockId' => $stockItem->stockId,
                    'quantity' => $stockItem->quantity,
                    'name' => $inventoryItems[$stockItem->stockId]->itemName ?? 'Unknown'
                ];
            })->values();

            return $menu;
        });

        $inventoryOptions = Inventory::where('restaurantId', $request->restaurantId)->get();

        return response()->json([
            'data' => [
                'menus' => $menus,
                'inventoryOptions' => $inventoryOptions,
            ],
            'message' => 'Menus and inventory options retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/menus/status",
     *     summary="Update menu item status",
     *     tags={"Menu"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status", "id"},
     *             @OA\Property(property="status", type="integer", enum={0,1}, example=1),
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Menu status updated successfully"),
     *             @OA\Property(property="menu", ref="#/components/schemas/Menu")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:0,1',
            'id' => 'required|integer|exists:menus,id'
        ]);

        $menu = Menu::findOrFail($request->id);
        $menu->status = $request->status;
        $menu->save();

        return response()->json([
            'message' => 'Menu status updated successfully',
            'menu' => $menu
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/menu",
     *     summary="Create a new menu item",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"itemName", "price", "categoryId", "restaurantId", "stockItems"},
     *                 @OA\Property(property="itemName", type="string", example="Pizza"),
     *                 @OA\Property(property="itemImage", type="file", description="Image file (max 2MB)"),
     *                 @OA\Property(property="price", type="number", format="float", example=9.99),
     *                 @OA\Property(property="categoryId", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="string", example="1"),
     *                 @OA\Property(property="sub_category", type="integer", example=1, nullable=true),
     *                 @OA\Property(
     *                     property="stockItems",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="stockId", type="integer", example=1),
     *                         @OA\Property(property="quantity", type="number", example=10.5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu item created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="menu", ref="#/components/schemas/Menu"),
     *                 @OA\Property(property="itemImage", type="string", example="/menus/pizza.jpg", nullable=true),
     *                 @OA\Property(
     *                     property="stockItems",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="stockId", type="integer", example=1),
     *                         @OA\Property(property="quantity", type="number", example=10.5)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Menu item created successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
{
    Log::info('Reached store method');

    try {
        $validatedData = $request->validate([
            'itemName' => 'required|string|max:255',
            'itemImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'categoryId' => 'required|integer|exists:categories,id',
            'restaurantId' => 'required|string|max:255',
            'sub_category' => 'nullable|integer',
            'stockItems' => 'required|array|min:1',
            'stockItems.*.stockId' => 'required|integer|exists:inventory,id',
            'stockItems.*.quantity' => 'required|numeric|min:0.001',
        ]);

        Log::info('Validated Data:', $validatedData);

        return DB::transaction(function () use ($validatedData, $request) {
            $menu = Menu::create([
                'itemName' => $validatedData['itemName'],
                'price' => $validatedData['price'],
                'categoryId' => $validatedData['categoryId'],
                'restaurantId' => $validatedData['restaurantId'],
                'sub_category' => $validatedData['sub_category'] ?? null,
                'status' => 1 // Default to active
            ]);

            if ($request->hasFile('itemImage')) {
                $imagePath = $request->file('itemImage')->store('menus', 'public');
                $menu->itemImage = $imagePath;
                $menu->save();
            }

            foreach ($validatedData['stockItems'] as $stockItem) {
                MenuInventory::create([
                    'menuId' => $menu->id,
                    'restaurantId' => $validatedData['restaurantId'],
                    'stockId' => $stockItem['stockId'],
                    'quantity' => $stockItem['quantity'],
                ]);
            }

            $imageUrl = $menu->itemImage ? Storage::url($menu->itemImage) : null;

            return response()->json([
                'data' => [
                    'menu' => $menu->fresh(),
                    'itemImage' => $imageUrl,
                    'stockItems' => $validatedData['stockItems'],
                ],
                'message' => 'Menu item created successfully'
            ], 201);
        });
    } catch (\Exception $e) {
        Log::error('Failed to create menu item:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to create menu item: ' . $e->getMessage()], 500);
    }
}

    /**
     * @OA\Put(
     *     path="/menu/update/{id}",
     *     summary="Update a menu item",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"itemName", "price", "categoryId"},
     *                 @OA\Property(property="itemName", type="string", example="Burger"),
     *                 @OA\Property(property="itemImage", type="file", description="Image file (max 2MB)"),
     *                 @OA\Property(property="price", type="number", format="float", example=5.99),
     *                 @OA\Property(property="categoryId", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Menu"),
     *             @OA\Property(property="message", type="string", example="Menu item updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu item not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, $id)
    {
        Log::info('Updating menu:', ['id' => $id]);

        $validatedData = $request->validate([
            'itemName' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categoryId' => 'required|integer',
            'itemImage' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $menu = Menu::findOrFail($id);

        return DB::transaction(function () use ($request, $menu, $validatedData) {
            if ($request->hasFile('itemImage')) {
                if ($menu->itemImage) {
                    Storage::disk('public')->delete($menu->itemImage);
                }
                $imagePath = $request->file('itemImage')->store('menus', 'public');
                $validatedData['itemImage'] = $imagePath;
            }

            $menu->update($validatedData);

            return response()->json([
                'data' => $menu->fresh(),
                'message' => 'Menu item updated successfully'
            ], 200);
        });
    }

    /**
     * @OA\Delete(
     *     path="/menu/{id}",
     *     summary="Delete a menu item",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Menu item deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu item not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        return DB::transaction(function () use ($menu) {
            if ($menu->itemImage) {
                Storage::disk('public')->delete($menu->itemImage);
            }

            MenuInventory::where('menuId', $menu->id)->delete();
            $menu->delete();

            return response()->json([
                'message' => 'Menu item deleted successfully'
            ], 200);
        });
    }
}

/**
 * @OA\Schema(
 *     schema="Inventory",
 *     type="object",
 *     title="Inventory",
 *     description="Inventory Model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="itemName", type="string", example="Cheese"),
 *     @OA\Property(property="restaurantId", type="string", example="1"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
