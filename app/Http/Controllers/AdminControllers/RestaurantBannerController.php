<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\RestaurantBanners;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class RestaurantBannerController extends Controller
{
    /**
     * List all restaurant banners
     *
     * Retrieve a paginated list of all restaurant banners.
     *
     * @OA\Get(
     *     path="/admin/banners",
     *     tags={"Banners"},
     *     summary="List all restaurant banners",
     *     description="Returns a paginated list of restaurant banners.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="restaurantId", type="string", example="rest_123", nullable=true),
     *                     @OA\Property(property="banner_1", type="string", example="https://i.ibb.co/xxx/banner1.jpg"),
     *                     @OA\Property(property="banner_2", type="string", example="https://i.ibb.co/xxx/banner2.jpg", nullable=true),
     *                     @OA\Property(property="banner_3", type="string", example="https://i.ibb.co/xxx/banner3.jpg", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-27T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-27T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to retrieve banners")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $banners = RestaurantBanners::paginate(10);
            return response()->json($banners, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve banners: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show a specific restaurant banner
     *
     * Retrieve details of a specific restaurant banner by ID.
     *
     * @OA\Get(
     *     path="/admin/banners/{id}",
     *     tags={"Banners"},
     *     summary="Get a specific restaurant banner",
     *     description="Returns details of a restaurant banner by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the banner",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="restaurantId", type="string", example="rest_123", nullable=true),
     *             @OA\Property(property="banner_1", type="string", example="https://i.ibb.co/xxx/banner1.jpg"),
     *             @OA\Property(property="banner_2", type="string", example="https://i.ibb.co/xxx/banner2.jpg", nullable=true),
     *             @OA\Property(property="banner_3", type="string", example="https://i.ibb.co/xxx/banner3.jpg", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-27T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-27T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Banner not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Banner not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to retrieve banner")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $banner = RestaurantBanners::findOrFail($id);
            return response()->json($banner, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Banner not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve banner: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Find restaurant banners by restaurant ID
     *
     * Retrieve a paginated list of restaurant banners associated with a specific restaurant ID.
     *
     * @OA\Get(
     *     path="/admin/banners/restaurant/{restaurantId}",
     *     tags={"Banners"},
     *     summary="Find banners by restaurant ID",
     *     description="Returns a paginated list of banners associated with the given restaurant ID.",
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="restaurantId", type="string", example="rest_123", nullable=true),
     *                     @OA\Property(property="banner_1", type="string", example="https://i.ibb.co/xxx/banner1.jpg"),
     *                     @OA\Property(property="banner_2", type="string", example="https://i.ibb.co/xxx/banner2.jpg", nullable=true),
     *                     @OA\Property(property="banner_3", type="string", example="https://i.ibb.co/xxx/banner3.jpg", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-27T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-27T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No banners found for this restaurant ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No banners found for this restaurant ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to retrieve banners")
     *         )
     *     )
     * )
     */
    public function findByRestaurantId($restaurantId)
    {
        try {
            $banners = RestaurantBanners::where('restaurantId', $restaurantId)->paginate(10);
            if ($banners->isEmpty()) {
                return response()->json(['error' => 'No banners found for this restaurant ID'], 404);
            }
            return response()->json($banners, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve banners: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload restaurant banners
     *
     * Upload up to three banner images for a restaurant, store them via ImgBB, and save their URLs in the database.
     *
     * @OA\Post(
     *     path="/admin/banners/upload",
     *     tags={"Banners"},
     *     summary="Upload restaurant banner images",
     *     description="Uploads banner images to ImgBB and saves their URLs in the database. Banner 1 is required, while Banner 2, Banner 3, and restaurantId are optional.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="restaurantId",
     *                     description="Restaurant ID (optional)",
     *                     type="string",
     *                     example="rest_123"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_1",
     *                     description="First banner image (required)",
     *                     type="file",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_2",
     *                     description="Second banner image (optional)",
     *                     type="file",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_3",
     *                     description="Third banner image (optional)",
     *                     type="file",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banners uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Banners uploaded successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="banner_1",
     *                     type="array",
     *                     @OA\Items(type="string", example="The banner_1 field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="restaurantId",
     *                     type="array",
     *                     @OA\Items(type="string", example="The restaurantId must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to upload banner_1")
     *         )
     *     )
     * )
     */
    public function uploadBanners(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'restaurantId' => 'nullable|string|max:255',
            'banner_1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ImgBB API key
        $apiKey = env('IMGBB_API_KEY');

        $banners = [];
        $fields = ['banner_1', 'banner_2', 'banner_3'];

        // Loop through each banner field
        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $image = $request->file($field);
                $imagePath = $image->getPathname();
                $imageName = $image->getClientOriginalName();

                // Upload to ImgBB
                $response = Http::attach(
                    'image', file_get_contents($imagePath), $imageName
                )->post("https://api.imgbb.com/1/upload", [
                    'key' => $apiKey,
                    'expiration' => 600,
                ]);

                if ($response->successful()) {
                    $banners[$field] = $response->json()['data']['url'];
                } else {
                    return response()->json(['error' => "Failed to upload $field"], 500);
                }
            }
        }

        // Include restaurantId if provided
        if ($request->has('restaurantId')) {
            $banners['restaurantId'] = $request->input('restaurantId');
        }

        // Save to database
        try {
            RestaurantBanners::create($banners);
            return response()->json(['message' => 'Banners uploaded successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save banners: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update restaurant banners
     *
     * Update the banner images and restaurant ID for a specific restaurant banner record by ID.
     *
     * @OA\Post(
     *     path="/admin/banners/{id}",
     *     tags={"Banners"},
     *     summary="Update restaurant banner images",
     *     description="Updates banner images and restaurant ID for a specific banner record. All fields are optional.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the banner to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="restaurantId",
     *                     description="Restaurant ID (optional)",
     *                     type="string",
     *                     example="rest_123"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_1",
     *                     description="First banner image (optional)",
     *                     type="file",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_2",
     *                     description="Second banner image (optional)",
     *                     type="file",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_3",
     *                     description="Third banner image (optional)",
     *                     type="file",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banners updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Banners updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Banner not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Banner not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="banner_1",
     *                     type="array",
     *                     @OA\Items(type="string", example="The banner_1 must be an image.")
     *                 ),
     *                 @OA\Property(
     *                     property="restaurantId",
     *                     type="array",
     *                     @OA\Items(type="string", example="The restaurantId must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to update banners")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $banner = RestaurantBanners::findOrFail($id);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'restaurantId' => 'nullable|string|max:255',
                'banner_1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'banner_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'banner_3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // ImgBB API key
            $apiKey = env('IMGBB_API_KEY');

            $banners = [];
            $fields = ['banner_1', 'banner_2', 'banner_3'];

            // Loop through each banner field
            foreach ($fields as $field) {
                if ($request->hasFile($field)) {
                    $image = $request->file($field);
                    $imagePath = $image->getPathname();
                    $imageName = $image->getClientOriginalName();

                    // Upload to ImgBB
                    $response = Http::attach(
                        'image', file_get_contents($imagePath), $imageName
                    )->post("https://api.imgbb.com/1/upload", [
                        'key' => $apiKey,
                        'expiration' => 600,
                    ]);

                    if ($response->successful()) {
                        $banners[$field] = $response->json()['data']['url'];
                    } else {
                        return response()->json(['error' => "Failed to upload $field"], 500);
                    }
                }
            }

            // Include restaurantId if provided
            if ($request->has('restaurantId')) {
                $banners['restaurantId'] = $request->input('restaurantId');
            }

            // Update only provided fields
            try {
                $banner->update($banners);
                return response()->json(['message' => 'Banners updated successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to update banners: ' . $e->getMessage()], 500);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Banner not found'], 404);
        }
    }

    /**
     * Delete a restaurant banner
     *
     * Delete a specific restaurant banner by ID.
     *
     * @OA\Delete(
     *     path="/admin/banners/{id}",
     *     tags={"Banners"},
     *     summary="Delete a restaurant banner",
     *     description="Deletes a restaurant banner by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the banner to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Banner deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Banner not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Banner not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to delete banner")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $banner = RestaurantBanners::findOrFail($id);
            $banner->delete();
            return response()->json(['message' => 'Banner deleted successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Banner not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete banner: ' . $e->getMessage()], 500);
        }
    }
}
