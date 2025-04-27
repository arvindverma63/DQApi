<?php

namespace App\Http\Controllers\Admincontrollers;

use App\Http\Controllers\Controller;
use App\Models\RestaurantBanners;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class RestaurantBannerController extends Controller
{
    /**
     * Upload restaurant banners
     *
     * Upload up to three banner images for a restaurant, store them via ImgBB, and save their URLs in the database.
     *
     * @OA\Post(
     *     path="/admin/banners/upload",
     *     tags={"Banners"},
     *     summary="Upload restaurant banner images",
     *     description="Uploads banner images to ImgBB and saves their URLs in the database. Banner 1 is required, while Banner 2 and Banner 3 are optional.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
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
            'banner_1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ImgBB API key
        $apiKey = env('IMGBB_API_KEY'); // Store your API key in .env

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
                    'expiration' => 600, // Optional: Set expiration time
                ]);

                if ($response->successful()) {
                    $banners[$field] = $response->json()['data']['url'];
                } else {
                    return response()->json(['error' => "Failed to upload $field"], 500);
                }
            }
        }

        // Save to database
        try {
            RestaurantBanners::create($banners);
            return response()->json(['message' => 'Banners uploaded successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save banners: ' . $e->getMessage()], 500);
        }
    }
}
