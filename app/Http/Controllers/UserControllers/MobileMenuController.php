<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuInventory;
use App\Models\Inventory;
use Illuminate\Http\Request;

class MobileMenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/app/menu",
     *     summary="Get menu for a specific restaurant",
     *     tags={"Menu For App"},
     *     operationId="getMenu",
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         description="ID of the restaurant",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of menu items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="number", example=1),
     *                 @OA\Property(property="itemName", type="string", example="Pizza"),
     *                 @OA\Property(property="itemImage", type="string", example="http://example.com/image.jpg"),
     *                 @OA\Property(property="price", type="number", example=9.99),
     *                 @OA\Property(property="category", type="string", example="Main Course"),
     *                 @OA\Property(
     *                     property="ingredients",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="ingredientName", type="string", example="Mozzarella Cheese"),
     *                         @OA\Property(property="quantity", type="number", example=1.5),
     *                         @OA\Property(property="unit", type="string", example="kg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Menu not found"
     *     )
     * )
     */
    public function getMenu(Request $request)
    {
        $request->validate([
            'restaurantId' => 'required|string',
        ]);

        $menuItems = Menu::where('restaurantId', $request->input('restaurantId'))->get();

        if ($menuItems->isEmpty()) {
            return response()->json(['message' => 'No menu found for the given restaurant ID'], 404);
        }

        $categoryIds = $menuItems->pluck('categoryId')->unique();
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $menuIds = $menuItems->pluck('id')->unique();
        $menuInventories = MenuInventory::whereIn('menuId', $menuIds)->get();
        $stockIds = $menuInventories->pluck('stockId')->unique();
        $inventories = Inventory::whereIn('id', $stockIds)->get()->keyBy('id');

        $data = [];

        foreach ($menuItems as $menu) {
            $category = $categories->get($menu->categoryId);

            $ingredients = $menuInventories->where('menuId', $menu->id)->map(function ($inventory) use ($inventories) {
                $ingredient = $inventories->get($inventory->stockId);
                return $ingredient ? [
                    'ingredientName' => $ingredient->itemName,
                    'quantity' => $inventory->quantity,
                    'unit' => $ingredient->unit,
                ] : null;
            })->filter()->values();

            $data[] = [
                'id' => $menu->id,
                'itemName' => $menu->itemName,
                'itemImage' => $menu->itemImage,
                'price' => $menu->price,
                'category' => $category ? $category->categoryName : null,
                'ingredients' => $ingredients,
            ];
        }

        return response()->json($data);
    }

    /**
     * @OA\Get(
     *     path="/app/menu/all",
     *     summary="Get all menu items",
     *     tags={"Menu For App"},
     *     operationId="getAllMenu",
     *     @OA\Response(
     *         response=200,
     *         description="List of all menu items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="number", example=1),
     *                 @OA\Property(property="itemName", type="string", example="Burger"),
     *                 @OA\Property(property="itemImage", type="string", example="http://example.com/image.jpg"),
     *                 @OA\Property(property="price", type="number", example=5.99),
     *                 @OA\Property(property="category", type="string", example="Snacks"),
     *                 @OA\Property(
     *                     property="ingredients",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="ingredientName", type="string", example="Lettuce"),
     *                         @OA\Property(property="quantity", type="number", example=0.5),
     *                         @OA\Property(property="unit", type="string", example="kg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No menu items found"
     *     )
     * )
     */
    public function getAllMenu()
    {
        $menuItems = Menu::all();

        if ($menuItems->isEmpty()) {
            return response()->json(['message' => 'No menu items found'], 404);
        }

        $categoryIds = $menuItems->pluck('categoryId')->unique();
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $menuIds = $menuItems->pluck('id')->unique();
        $menuInventories = MenuInventory::whereIn('menuId', $menuIds)->get();
        $stockIds = $menuInventories->pluck('stockId')->unique();
        $inventories = Inventory::whereIn('id', $stockIds)->get()->keyBy('id');

        $data = [];

        foreach ($menuItems as $menu) {
            $category = $categories->get($menu->categoryId);

            $ingredients = $menuInventories->where('menuId', $menu->id)->map(function ($inventory) use ($inventories) {
                $ingredient = $inventories->get($inventory->stockId);
                return $ingredient ? [
                    'ingredientName' => $ingredient->itemName,
                    'quantity' => $inventory->quantity,
                    'unit' => $ingredient->unit,
                ] : null;
            })->filter()->values();

            $data[] = [
                'id' => $menu->id,
                'itemName' => $menu->itemName,
                'itemImage' => $menu->itemImage,
                'price' => $menu->price,
                'category' => $category ? $category->categoryName : null,
                'ingredients' => $ingredients,
            ];
        }

        return response()->json($data);
    }
}
