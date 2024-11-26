<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * @OA\Post(
     *     path="/category",
     *     summary="Add a new category",
     *     tags={"Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"categoryName", "restaurantId"},
     *                 @OA\Property(property="categoryName", type="string", example="Beverages"),
     *                 @OA\Property(property="categoryImage", type="file", description="Category Image"),
     *                 @OA\Property(property="restaurantId", type="string", example="1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Category created successfully")
     *         )
     *     )
     * )
     */
    public function addCategory(Request $request)
{
    // Validate request
    $validatedData = $request->validate([
        'categoryName' => 'string|required',
        'categoryImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
        'restaurantId'  => 'string|required'
    ]);

    // Handle image upload
    if ($request->hasFile('categoryImage')) {
        $imageName = time() . '_' . $request->categoryName . '.' . $request->categoryImage->extension();
        // Store image in public folder
        $request->categoryImage->move(public_path('images/categories'), $imageName);
        $validatedData['categoryImage'] = 'images/categories/' . $imageName; // Store image path in database
    }

    // Create the category in the database with the validated data
    $category = Category::create($validatedData);

    // Get the full URL for the image
    $imageUrl = env('APP_URL').'/'.$category['categoryImage'];

    // Prepare response data
    $data = [
        'categoryName' => $category->categoryName,
        'categoryImage' => $imageUrl, // Send full image URL
        'id' => $category->id,
        'restaurantId' => $category->restaurantId
    ];

    // Return a JSON response
    return response()->json(['data' => $data, 'message' => 'Category created successfully']);
}




    /**
 * @OA\Get(
 *     path="/categories",
 *     summary="Get all categories",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="restaurantId",
 *         in="query",
 *         required=true,
 *         description="Restaurant ID",
 *         @OA\Schema(type="string", example="1")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Categories retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="message", type="string", example="Categories retrieved successfully")
 *         )
 *     )
 * )
 */

 public function getAllCategories(Request $request)
 {
     // Validate restaurantId
     $validatedData = $request->validate([
         'restaurantId' => 'string|required'
     ]);

     // Fetch categories based on restaurantId
     $categories = Category::where('restaurantId', $validatedData['restaurantId'])->get();

     // Initialize an empty array for the response data
     $data = [];

     // Construct image URL for each category and prepare the response
     foreach ($categories as $category) {
         $imageUrl = env('APP_URL') . '/' . $category->categoryImage; // Construct image URL

         $data[] = [
             'id' => $category->id,
             'categoryName' => $category->categoryName,
             'categoryImage' => $imageUrl, // Send full image URL
             'created_at' => $category->created_at,
             'updated_at' => $category->updated_at,
             'restaurantId' => $category->restaurantId
         ];
     }

     // Return the response in JSON format
     return response()->json($data);
 }


    /**
     * @OA\Get(
     *     path="/category/{id}",
     *     summary="Get a single category by ID",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */
    public function getCategoryById($id)
    {
        $category = Category::find($id);

        if ($category) {
            return response()->json(['data' => $category, 'message' => 'Category retrieved successfully']);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }

    /**
     * @OA\Put(
     *     path="/category/{id}",
     *     summary="Update a category",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"categoryName", "restaurantId"},
     *                 @OA\Property(property="categoryName", type="string", example="Updated Category"),
     *                 @OA\Property(property="categoryImage", type="file", description="Category Image"),
     *                 @OA\Property(property="restaurantId", type="string", example="2")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Category updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */
    public function updateCategory(Request $request, $id)
    {
        // Validate request
        $validatedData = $request->validate([
            'categoryName' => 'string|required',
            'categoryImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
            'restaurantId'  => 'string|required'
        ]);

        // Find the category
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Handle image upload
        if ($request->hasFile('categoryImage')) {
            // Delete old image if exists
            if ($category->categoryImage && file_exists(public_path($category->categoryImage))) {
                unlink(public_path($category->categoryImage));
            }

            // Upload new image
            $imageName = time() . '_' . $request->categoryName . '.' . $request->categoryImage->extension();
            $request->categoryImage->move(public_path('images/categories'), $imageName);
            $validatedData['categoryImage'] = 'images/categories/' . $imageName; // Store image path
        }

        // Update category
        $category->update($validatedData);

        return response()->json(['data' => $category, 'message' => 'Category updated successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/category/{id}",
     *     summary="Delete a category",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */
    public function deleteCategory($id)
    {
        $category = Category::find($id);

        if ($category) {
            // Delete the image from storage
            if ($category->categoryImage && file_exists(public_path($category->categoryImage))) {
                unlink(public_path($category->categoryImage));
            }

            // Delete category
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }
}
