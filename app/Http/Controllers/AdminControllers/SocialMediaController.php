<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;


class SocialMediaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/social-media",
     * tags={"SocialMedia"},
     *     summary="Get all social media records",
     *     @OA\Response(response=200, description="List of social media records")
     * )
     */
    public function index()
    {
        $socialMedias = SocialMedia::all();
        return response()->json($socialMedias);
    }

    /**
     * @OA\Post(
     *     path="/social-media",
     * tags={"SocialMedia"},
     *     summary="Create a new social media record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "influencer"},
     *             @OA\Property(property="restaurantId", type="string"),
     *             @OA\Property(property="influencer", type="string"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="insta", type="string"),
     *             @OA\Property(property="facebook", type="string"),
     *             @OA\Property(property="youtube", type="string"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="offers", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Record created successfully")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'restaurantId' => 'string',
            'influencer' => 'required|string',
            'details' => 'nullable|string',
            'insta' => 'nullable|string',
            'facebook' => 'nullable|string',
            'youtube' => 'nullable|string',
            'location' => 'nullable|string',
            'offers' => 'nullable|string',
            'price' => 'nullable|numeric',
            'available' => 'nullable|boolean',
        ]);

        $socialMedia = SocialMedia::create($request->all());
        return response()->json($socialMedia, 201);
    }

    /**
     * @OA\Get(
     *     path="/social-media/{id}",
     * tags={"SocialMedia"},
     *     summary="Get a single social media record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Single social media record")
     * )
     */
    public function show($id)
    {
        $socialMedia = SocialMedia::findOrFail($id);
        return response()->json($socialMedia);
    }

    /**
     * @OA\Put(
     *     path="/social-media/{id}",
     * tags={"SocialMedia"},
     *     summary="Update a social media record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="restaurantId", type="string"),
     *             @OA\Property(property="influencer", type="string"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="insta", type="string"),
     *             @OA\Property(property="facebook", type="string"),
     *             @OA\Property(property="youtube", type="string"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="offers", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Record updated successfully")
     * )
     */
    public function update(Request $request, $id)
    {
        $socialMedia = SocialMedia::findOrFail($id);

        $request->validate([
            'restaurantId' => 'string',
            'influencer' => 'string',
            'details' => 'nullable|string',
            'insta' => 'nullable|string',
            'facebook' => 'nullable|string',
            'youtube' => 'nullable|string',
            'location' => 'nullable|string',
            'offers' => 'nullable|string',
            'price' => 'nullable|numeric',
            'available' => 'nullable|boolean',
        ]);

        $socialMedia->update($request->all());
        return response()->json($socialMedia);
    }

    /**
     * @OA\Delete(
     *     path="/social-media/{id}",
     * tags={"SocialMedia"},
     *     summary="Delete a social media record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Record deleted successfully")
     * )
     */
    public function destroy($id)
    {
        $socialMedia = SocialMedia::findOrFail($id);
        $socialMedia->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }
}
