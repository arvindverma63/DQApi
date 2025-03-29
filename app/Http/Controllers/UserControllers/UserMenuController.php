<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\MenuInventory;
use Illuminate\Http\Request;

class UserMenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users/menu",
     *     summary="Get paginated menu items for a restaurant",
     *     description="Retrieve a paginated list of menu items for a specific restaurant, including categories and ingredients.",
     *     tags={"App Menu"},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         description="ID of the restaurant to fetch menu items for",
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
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="itemName", type="string", example="Pizza"),
     *                 @OA\Property(property="itemImage", type="string", example="http://example.com/pizza.jpg"),
     *                 @OA\Property(property="price", type="number", format="float", example=9.99),
     *                 @OA\Property(property="category", type="string", example="Main Course"),
     *                 @OA\Property(property="ingredients", type="array", @OA\Items(
     *                     @OA\Property(property="ingredientName", type="string", example="Cheese"),
     *                     @OA\Property(property="quantity", type="number", format="float", example=0.5),
     *                     @OA\Property(property="unit", type="string", example="kg")
     *                 ))
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="total", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Invalid restaurantId",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid restaurantId")
     *         )
     *     )
     * )
     */
    public function getMenu(Request $request)
    {
        // Validate restaurantId
        $request->validate([
            'restaurantId' => 'required|string',
        ]);

        // Paginate menu items
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $menuItems = Menu::where('restaurantId', $request->input('restaurantId'))
            ->where('status', 0)
            ->paginate($perPage);

        $sub_categoryId = $menuItems->pluck('sub_category')->unique();
        $categoryIds = $menuItems->pluck('categoryId')->unique();
        $sub_category = Category::whereIn('id',$sub_categoryId)->get()->keyBy('id');
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
                'subcategory' => $sub_category ? $sub_category->sub_category_name : null,
                'ingredients' => $ingredients,
            ];
        }

        // Return paginated response
        return response()->json([
            'data' => $data,
            'current_page' => $menuItems->currentPage(),
            'last_page' => $menuItems->lastPage(),
            'per_page' => $menuItems->perPage(),
            'total' => $menuItems->total(),
        ]);
    }
}
