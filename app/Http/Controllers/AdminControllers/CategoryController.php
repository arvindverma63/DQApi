<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Menu;
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
     *                 @OA\Property(property="categoryImage", type="string", example="/storage/categories/image.jpg", nullable=true),
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
     *     @OA\Response(response=422, description="Validation error")
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

        if ($request->hasFile('categoryImage')) {
            $imagePath = $request->file('categoryImage')->store('categories', 'public');
            $validatedData['categoryImage'] = $imagePath;
        }

        $category = Category::create($validatedData);
        $imageUrl = $category->categoryImage ? Storage::url($category->categoryImage) : null;

        $response = [
            'id' => $category->id,
            'categoryName' => $category->categoryName,
            'categoryImage' => $imageUrl,
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
     *                     @OA\Property(property="categoryImage", type="string", example="/storage/categories/image.jpg", nullable=true),
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
        $validatedData = $request->validate([
            'restaurantId' => 'required|string'
        ]);

        $categories = Category::where('restaurantId', $validatedData['restaurantId'])->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'categoryName' => $category->categoryName,
                'categoryImage' => $category->categoryImage ? Storage::url($category->categoryImage) : null,
                'restaurantId' => $category->restaurantId,
                'sub_category_id' => $category->sub_category_id,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at
            ];
        });

        return response()->json([
            'data' => $data,
            'message' => 'Categories retrieved successfully'
        ], 200);
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
     *                 @OA\Property(property="categoryImage", type="string", example="/storage/categories/image.jpg", nullable=true),
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
            'categoryImage' => $category->categoryImage ? Storage::url($category->categoryImage) : null,
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
     *                 @OA\Property(property="categoryImage", type="string", example="/storage/categories/image.jpg", nullable=true),
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
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validatedData = $request->validate([
            'categoryName' => 'sometimes|required|string|max:255',
            'categoryImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'restaurantId' => 'sometimes|required|string',
            'sub_category_id' => 'nullable|integer|exists:sub_categories,id',
        ]);

        if ($request->hasFile('categoryImage')) {
            if ($category->categoryImage) {
                Storage::disk('public')->delete($category->categoryImage);
            }
            $imagePath = $request->file('categoryImage')->store('categories', 'public');
            $validatedData['categoryImage'] = $imagePath;
        }

        $category->update($validatedData);

        $response = [
            'id' => $category->id,
            'categoryName' => $category->categoryName,
            'categoryImage' => $category->categoryImage ? Storage::url($category->categoryImage) : null,
            'restaurantId' => $category->restaurantId,
            'sub_category_id' => $category->sub_category_id,
        ];

        return response()->json([
            'data' => $response,
            'message' => 'Category updated successfully'
        ], 200);
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

        if ($category->categoryImage) {
            Storage::disk('public')->delete($category->categoryImage);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
