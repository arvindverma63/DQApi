<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="User Profile API",
 *     description="API for managing user profiles in the system",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 */

/**
 * @OA\Tag(
 *     name="Restaurant Profile",
 *     description="Operations related to user profiles"
 * )
 */
class UserProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/rest-profile/{id}",
     *     summary="Get User Profile by Restaurant ID",
     *     tags={"Restaurant Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="R1728231298"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="restaurantId", type="string", example="R1728231298"),
     *             @OA\Property(property="userName", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User profile not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Profile not found")
     *         )
     *     )
     * )
     */
    public function getProfile($id){
        $response = UserProfile::where('restaurantId', $id)->first();

        if($response) {
            return response()->json($response);
        }

        return response()->json(['error' => 'Profile not found'], 404);
    }
}
