<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;


class SubCategoryController extends Controller
{
    /**
     * @OA\Post(
     *     path="/admin/subcategories",
     *     summary="Create a new subcategory",
     *     tags={"SubCategories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "category_name", "category_id"},
     *             @OA\Property(property="restaurantId", type="string", example="R23424234"),
     *             @OA\Property(property="category_name", type="string", example="Beverages"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(@OA\Property(property="success", type="string"))),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function addCategory(Request $request)
    {
        $validated = $request->validate([
            'restaurantId' => 'required|integer',
            'category_name' => 'required|string|max:255',
            'category_id' => 'required|integer'
        ]);

        $subCategory = SubCategory::create($validated);

        return response()->json([
            'success' => 'category added successfully',
            'data' => $subCategory
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/admin/subcategories",
     *     summary="Get all subcategories",
     *     tags={"SubCategories"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="integer", example=1),
     *                 @OA\Property(property="sub_category_name", type="string", example="Beverages"),
     *                 @OA\Property(property="category_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function getAllCategories()
    {
        $subCategories = SubCategory::all();
        return response()->json($subCategories, 200);
    }

    /**
     * @OA\Get(
     *     path="/admin/subcategories/{id}",
     *     summary="Get a specific subcategory",
     *     tags={"SubCategories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="restaurantId", type="integer", example=1),
     *             @OA\Property(property="sub_category_name", type="string", example="Beverages"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Subcategory not found")
     * )
     */
    public function getCategory($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        return response()->json($subCategory, 200);
    }

    /**
     * @OA\Put(
     *     path="/admin/subcategories/{id}",
     *     summary="Update a subcategory",
     *     tags={"SubCategories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="restaurantId", type="string", example="R2342343"),
     *             @OA\Property(property="category_name", type="string", example="Beverages"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(@OA\Property(property="success", type="string"))),
     *     @OA\Response(response=404, description="Subcategory not found")
     * )
     */
    public function updateCategory(Request $request, $id)
    {
        $subCategory = SubCategory::findOrFail($id);

        $validated = $request->validate([
            'restaurantId' => 'sometimes|integer',
            'category_name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|integer'
        ]);

        $subCategory->update($validated);

        return response()->json([
            'success' => 'category updated successfully',
            'data' => $subCategory
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/admin/subcategories/{id}",
     *     summary="Delete a subcategory",
     *     tags={"SubCategories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(@OA\Property(property="success", type="string"))),
     *     @OA\Response(response=404, description="Subcategory not found")
     * )
     */
    public function deleteCategory($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->delete();

        return response()->json([
            'success' => 'category deleted successfully'
        ], 200);
    }
}
