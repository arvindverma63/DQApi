<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Support\Facades\Http;

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
     *                 @OA\Property(property="categoryImage", type="file", description="Category Image (max 2MB)"),
     *                 @OA\Property(property="restaurantId", type="string", example="1"),
     *                 @OA\Property(property="sub_category_id", type="integer", example=1, description="Optional subcategory ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="categoryName", type="string", example="Beverages"),
     *                 @OA\Property(property="categoryImage", type="string", example="https://i.ibb.co/example.jpg", nullable=true),
     *                 @OA\Property(property="restaurantId", type="string", example="1"),
     *                 @OA\Property(property="sub_category_id", type="integer", example=1, nullable=true)
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category created successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function addCategory(Request $request)
    {
        $validatedData = $request->validate([
            'categoryName' => 'required|string|max:255',
            'categoryImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'restaurantId' => 'required|string',
            'sub_category_id' => 'nullable|integer|exists:sub_categories,id',
        ]);

        if ($request->hasFile('categoryImage') && $request->file('categoryImage')->isValid()) {
            // Prepare image for ImgBB upload
            $imageName = time() . '_' . $request->categoryName . '.' . $request->file('categoryImage')->getClientOriginalExtension();

            // Upload to ImgBB
            $response = Http::attach(
                'image',
                file_get_contents($request->file('categoryImage')->getRealPath()),
                $imageName
            )->post('https://api.imgbb.com/1/upload', [
                'key' => 'eb1e667c36413784234cf2e9b5081159'
            ]);

            // Check if upload was successful
            if ($response->failed() || !$response->json('data.url')) {
                return response()->json(['message' => 'Failed to upload image to ImgBB'], 500);
            }

            $validatedData['categoryImage'] = $response->json('data.url');
        }

        // Create the category in the database with the validated data
        $category = Category::create($validatedData);

        $response = [
            'id' => $category->id,
            'categoryName' => $category->categoryName,
            'categoryImage' => $category->categoryImage ?? null,
            'restaurantId' => $category->restaurantId,
            'sub_category_id' => $category->sub_category_id,
        ];

        return response()->json([
            'data' => $response,
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Get all categories",
     *     tags={"Category"},
     *     @OA\Parameter(name="restaurantId", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="categoryName", type="string", example="Beverages"),
     *                     @OA\Property(property="categoryImage", type="string", example="https://i.ibb.co/example.jpg", nullable=true),
     *                     @OA\Property(property="restaurantId", type="string", example="1"),
     *                     @OA\Property(property="sub_category_id", type="integer", example=1, nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categories retrieved successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
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

        // Prepare the response
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->id,
                'categoryName' => $category->categoryName,
                'categoryImage' => $category->categoryImage ?? null,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'restaurantId' => $category->restaurantId,
                'subcategory' => $category->sub_category ?? null,
            ];
        }

        // Return the response in JSON format
        return response()->json(['data' => $data, 'message' => 'Categories retrieved successfully']);
    }

    /**
     * @OA\Get(
     *     path="/category/{id}",
     *     summary="Get a single category by ID",
     *     tags={"Category"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="categoryName", type="string", example="Beverages"),
     *                 @OA\Property(property="categoryImage", type="string", example="https://i.ibb.co/example.jpg", nullable=true),
     *                 @OA\Property(property="restaurantId", type="string", example="1"),
     *                 @OA\Property(property="sub_category_id", type="integer", example=1, nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category retrieved successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function getCategoryById($id)
    {
        $category = Category::findOrFail($id);

        $response = [
            'id' => $category->id,
            'categoryName' => $category->categoryName,
            'categoryImage' => $category->categoryImage ?? null,
            'restaurantId' => $category->restaurantId,
            'sub_category_id' => $category->sub_category_id,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at
        ];

        return response()->json([
            'data' => $response,
            'message' => 'Category retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/category/{id}",
     *     summary="Update a category",
     *     tags={"Category"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="categoryName", type="string", example="Updated Category"),
     *                 @OA\Property(property="categoryImage", type="file", description="Category Image (max 2MB)"),
     *                 @OA\Property(property="restaurantId", type="string", example="2"),
     *                 @OA\Property(property="sub_category_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="categoryName", type="string", example="Updated Category"),
     *                 @OA\Property(property="categoryImage", type="string", example="https://i.ibb.co/example.jpg", nullable=true),
     *                 @OA\Property(property="restaurantId", type="string", example="2"),
     *                 @OA\Property(property="sub_category_id", type="integer", example=1, nullable=true)
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category updated successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function updateCategory(Request $request, $id)
    {
        // Validate request
        $validatedData = $request->validate([
            'categoryName' => 'string|required',
            'categoryImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'restaurantId' => 'string|required',
            'sub_category_id' => 'integer|nullable',
        ]);

        // Find the category
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Handle image upload
        if ($request->hasFile('categoryImage') && $request->file('categoryImage')->isValid()) {
            // Upload to ImgBB
            $imageName = time() . '_' . $request->categoryName . '.' . $request->file('categoryImage')->getClientOriginalExtension();
            $response = Http::attach(
                'image',
                file_get_contents($request->file('categoryImage')->getRealPath()),
                $imageName
            )->post('https://api.imgbb.com/1/upload', [
                'key' => 'eb1e667c36413784234cf2e9b5081159'
            ]);

            // Check if upload was successful
            if ($response->failed() || !$response->json('data.url')) {
                return response()->json(['message' => 'Failed to upload image to ImgBB'], 500);
            }

            $validatedData['categoryImage'] = $response->json('data.url');
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
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Category has linked menus"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);

        if (Menu::where('categoryId', $category->id)->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with linked menu items'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
