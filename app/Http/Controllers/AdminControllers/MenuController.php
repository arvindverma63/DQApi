<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\MenuInventory;
use Illuminate\Auth\Events\Validated;
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
 *     @OA\Property(property="id", type="integer", example=1, description="ID of the menu item"),
 *     @OA\Property(property="itemName", type="string", example="Pizza", description="Name of the menu item"),
 *     @OA\Property(property="itemImage", type="string", example="menu_images/pizza.jpg", description="Image path for the menu item"),
 *     @OA\Property(property="price", type="number", format="float", example=9.99, description="Price of the menu item"),
 *     @OA\Property(property="categoryId", type="string", example="1", description="Category ID of the menu item"),
 *     @OA\Property(property="restaurantId", type="string", example="1", description="Restaurant ID associated with the menu item"),
 *     @OA\Property(property="stock", type="integer", example=100, description="Available stock for the menu item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the menu item was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the menu item was last updated")
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
     *         description="The restaurant ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List all menu items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Menu")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'restaurantId' => 'required|string',
        ]);

        // Fetch menu items for the given restaurantId
        $menus = Menu::where('restaurantId', $request->restaurantId)->get();

        // Retrieve stock items from MenuInventory related to these menus
        $stockItems = MenuInventory::whereIn('menuId', $menus->pluck('id'))->get();

        // Fetch related inventory items for stock names
        $inventoryItems = Inventory::whereIn('id', $stockItems->pluck('stockId'))->get()->keyBy('id');

        // Transform each menu item to include full image URLs and stock item names
        $menus->transform(function ($menu) use ($stockItems, $inventoryItems) {
            if ($menu->itemImage) {
                // Correctly generate the public URL for the image stored in the 'public/menus' folder
                $menu->itemImage = url('menus/' . basename($menu->itemImage));
            }

            // Map stock items for the specific menu and include itemName from Inventory
            $menu->stockItems = $stockItems->where('menuId', $menu->id)->map(function ($stockItem) use ($inventoryItems) {
                return [
                    'stockId' => $stockItem->stockId,
                    'quantity' => $stockItem->quantity,
                    'name' => $inventoryItems[$stockItem->stockId]->itemName ?? '', // Include itemName from Inventory if available
                ];
            })->values();

            return $menu;
        });

        // Fetch all inventory items for the dropdown options
        $inventoryOptions = Inventory::all(['id', 'itemName']);

        // Return JSON response with both menu items and inventory options
        return response()->json([
            'data' => [
                'menus' => $menus,
                'inventoryOptions' => $inventoryOptions,
            ],
            'message' => 'Menus and inventory options retrieved successfully.',
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
     *                 required={"itemName", "price", "categoryId", "restaurantId", "stock", "stockItems"},
     *                 @OA\Property(property="itemName", type="string", example="Pizza", description="Name of the menu item"),
     *                 @OA\Property(property="itemImage", type="string", format="binary", description="Menu item image"),
     *                 @OA\Property(property="price", type="number", format="float", example=9.99, description="Price of the menu item"),
     *                 @OA\Property(property="categoryId", type="string", example="1", description="Category ID for the menu item"),
     *                 @OA\Property(property="restaurantId", type="string", example="1", description="Restaurant ID for the menu item"),
     *                 @OA\Property(property="stock", type="integer", example=100, description="Available stock for the menu item"),
     *                 @OA\Property(
     *                     property="stockItems",
     *                     type="array",
     *                     description="Array of stock items with their quantities",
     *                     @OA\Items(
     *                         @OA\Property(property="stockId", type="integer", example=1, description="Stock ID"),
     *                         @OA\Property(property="quantity", type="number", example=10.5, description="Quantity for this stock item")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu item created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Menu"),
     *             @OA\Property(property="message", type="string", example="Menu item created successfully with associated stock.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The total stock must be equal to or greater than the sum of stock item quantities.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create menu item and stock: [Error message here]")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        Log::info('Reached store method');

        try {
            // Validate the request
            $validatedData = $request->validate([
                'itemName' => 'required|string|max:255',
                'itemImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'price' => 'required|numeric|min:0',
                'categoryId' => 'required|integer',
                'restaurantId' => 'required|string|max:255',
                'stockItems' => 'required|array|min:1',
                'stockItems.*.stockId' => 'required|integer',
                'stockItems.*.quantity' => 'required|numeric|min:0.001',
            ]);

            Log::info('Validated Data:', $validatedData);

            // Start a database transaction
            DB::beginTransaction();

            // Create the menu item
            $menu = Menu::create([
                'itemName' => $validatedData['itemName'],
                'price' => $validatedData['price'],
                'categoryId' => $validatedData['categoryId'],
                'restaurantId' => $validatedData['restaurantId'],
            ]);

            Log::info('Menu created:', $menu->toArray());

            // Handle image upload if present
            if ($request->hasFile('itemImage') && $request->file('itemImage')->isValid()) {
                // Store the image directly in the public/menus folder
                $imageName = time() . '_' . $request->file('itemImage')->getClientOriginalName();
                $publicPath = public_path('menus');

                // Create the directory if it doesn't exist
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0777, true);
                }

                // Move the file to the public/menus directory
                $request->file('itemImage')->move($publicPath, $imageName);

                // Generate the public URL manually
                $publicImageUrl = url('menus/' . $imageName);
                $menu->update(['itemImage' => $publicImageUrl]);

                Log::info('Image uploaded and stored in public folder:', ['path' => $publicImageUrl]);
            }

            // Check and log each stock item being added to the MenuInventory table
            foreach ($validatedData['stockItems'] as $stockItem) {
                try {
                    Log::info('Processing stock item:', $stockItem);

                    $menuInventory = MenuInventory::create([
                        'menuId' => $menu->id,
                        'restaurantId' => $validatedData['restaurantId'],
                        'stockId' => $stockItem['stockId'],
                        'quantity' => $stockItem['quantity'],
                    ]);

                    Log::info('Stock item created for menu in inventory:', [
                        'menuId' => $menu->id,
                        'stockId' => $stockItem['stockId'],
                        'quantity' => $stockItem['quantity']
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error adding stock item to inventory:', [
                        'error' => $e->getMessage(),
                        'menuId' => $menu->id,
                        'stockId' => $stockItem['stockId'],
                        'quantity' => $stockItem['quantity']
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();
            Log::info('Transaction committed successfully.');

            // Return success response in JSON format with the image URL and stock items
            return response()->json([
                'data' => [
                    'menu' => $menu,
                    'itemImage' => ((!str_starts_with($menu->itemImage, 'http')) ? url($menu->itemImage) : $menu->itemImage) ?? null,
                    'stockItems' => $validatedData['stockItems'],
                ],
                'message' => 'Menu item created successfully with associated stock.'
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            Log::error('Failed to create menu item and associated stock items:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create menu item and stock: ' . $e->getMessage()], 500);
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
 *         description="Menu item ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"itemName", "price", "categoryId"},
 *                 @OA\Property(
 *                     property="itemName",
 *                     type="string",
 *                     example="Burger",
 *                     description="Name of the menu item"
 *                 ),
 *                 @OA\Property(
 *                     property="itemImage",
 *                     type="file",
 *                     description="Image of the menu item (optional)"
 *                 ),
 *                 @OA\Property(
 *                     property="price",
 *                     type="number",
 *                     format="float",
 *                     example=5.99,
 *                     description="Price of the menu item"
 *                 ),
 *                 @OA\Property(
 *                     property="categoryId",
 *                     type="integer",
 *                     example=1,
 *                     description="Category ID of the menu item"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Menu item updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", ref="#/components/schemas/Menu"),
 *             @OA\Property(property="itemImage", type="string", description="URL of the updated menu item image"),
 *             @OA\Property(property="message", type="string", example="Menu item updated successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Validation error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Authorization token not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Menu item not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Menu item not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update menu item",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Failed to update menu item")
 *         )
 *     )
 * )
 */


 public function update(Request $request, $id)
 {
     Log::info('Request received for updating menu:', $request->except('itemImage'));

     // Validate the incoming request
     $request->validate([
         'itemName' => 'required|string|max:255',
         'price' => 'required|numeric|min:0',
         'categoryId' => 'required|integer|exists:categories,id',
         'itemImage' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
     ]);

     // Find the menu item by ID
     $menu = Menu::find($id);

     if (!$menu) {
         return response()->json(['message' => 'Menu item not found'], 404);
     }

     try {
         DB::transaction(function () use ($request, $menu) {
             // Handle item image update
             if ($request->hasFile('itemImage')) {


                 $imageName = time() . '_' . $request->file('itemImage')->getClientOriginalName();
                 $imagePath = public_path('menus');

                 if (!file_exists($menu->itemImage)) {
                     mkdir($menu->itemImage, 0777, true);
                 }
                 if($menu->itemImage && !str_starts_with($menu->itemImage, 'http') && !is_dir($menu->itemImage)){
                     unlink($menu->itemImage);
                 }elseif($menu->itemImage){
                    $imageFromUrl = implode('/', array_slice(explode('/', trim(parse_url($menu->itemImage, PHP_URL_PATH), '/')), -2));
                    if(strpos($imageFromUrl, '.') !== false && !is_dir($imageFromUrl)){
                        unlink($imageFromUrl);
                    }
                 }

                 $request->file('itemImage')->move($imagePath, $imageName);
                 $publicImageUrl = 'menus/' . $imageName;

                 $request['itemImage'] = $publicImageUrl;
                 $request->itemImage = $publicImageUrl;

             }

             // Update menu details
            //  $menu->update($request->only(['itemName', 'price', 'categoryId', 'itemImage']));
             $menu->update([
                'itemImage' => $request->itemImage,
                'price' => $request->price,
                'categoryId' => $request->categoryId,
                'itemImage' => $request->itemImage
             ]);
         });

         return response()->json([
             'data' => $menu,
             'message' => 'Menu item updated successfully',
         ], 200);
     } catch (\Exception $e) {
         Log::error('Error updating menu item:', [
             'error' => $e->getMessage(),
             'request_data' => $request->except('itemImage'),
         ]);

         return response()->json(['message' => 'Failed to update menu item : '. $e->getMessage()], 500);
     }
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
     *         description="Menu item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Menu item deleted successfully"
     *     ),
     *     @OA\Response(response=404, description="Menu item not found")
     * )
     */

    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu item not found'], 404);
        }

        try {
            DB::transaction(function () use ($menu, $id) {
                // Delete the associated image if it exists
                if($menu->itemImage && !str_starts_with($menu->itemImage, 'http') && !is_dir($menu->itemImage)){
                    unlink($menu->itemImage);

                }elseif($menu->itemImage){
                   $imageFromUrl = implode('/', array_slice(explode('/', trim(parse_url($menu->itemImage, PHP_URL_PATH), '/')), -2));

                   if(strpos($imageFromUrl, '.') !== false && !is_dir($imageFromUrl)){
                       unlink($imageFromUrl);
                    }
                }

                // Delete the menu item
                $menu->delete();

                // Remove entries from MenuInventory
               MenuInventory::where('menuId', $id)->delete();
            });

            return response()->json(null, 204);
        } catch (\Exception $e) {
            // Log the error if necessary
            return response()->json(['message' => 'Failed to delete the menu item'], 500);
        }
    }
}
