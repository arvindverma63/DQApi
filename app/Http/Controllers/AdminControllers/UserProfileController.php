<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
/**
 * @OA\Schema(
 *     schema="Profile",
 *     type="object",
 *     required={"restaurantId", "email"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="User profile ID",
 *         example=4
 *     ),
 *     @OA\Property(
 *         property="firstName",
 *         type="string",
 *         description="User's first name",
 *         nullable=true,
 *         example="John"
 *     ),
 *     @OA\Property(
 *         property="lastName",
 *         type="string",
 *         description="User's last name",
 *         nullable=true,
 *         example="Doe"
 *     ),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         description="Gender of the user",
 *         nullable=true,
 *         example="Male"
 *     ),
 *     @OA\Property(
 *         property="restName",
 *         type="string",
 *         description="Restaurant name",
 *         nullable=true,
 *         example="Doe's Restaurant"
 *     ),
 *     @OA\Property(
 *         property="phoneNumber",
 *         type="string",
 *         description="User's phone number",
 *         nullable=true,
 *         example="1234567890"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         description="User's address",
 *         nullable=true,
 *         example="123 Main St"
 *     ),
 *     @OA\Property(
 *         property="pinCode",
 *         type="string",
 *         description="User's pin code",
 *         nullable=true,
 *         example="123456"
 *     ),
 *     @OA\Property(
 *         property="restaurantId",
 *         type="string",
 *         description="Restaurant identifier",
 *         example="R1728231298"
 *     ),
 *     @OA\Property(
 *         property="identity",
 *         type="string",
 *         description="Identity type",
 *         nullable=true,
 *         example="Passport"
 *     ),
 *     @OA\Property(
 *         property="identityNumber",
 *         type="string",
 *         description="Identity number",
 *         nullable=true,
 *         example="P123456789"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="User's email address",
 *         example="johndoe@example.com"
 *     )
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
    public function getProfile($id)
    {
        $response = UserProfile::where('restaurantId', $id)->first();

        if ($response) {
            $response->image = url($response->image);
            return response()->json($response);
        }

        return response()->json(['error' => 'Profile not found'], 404);
    }

    /**
     * @OA\Put(
     *     path="/profile/{id}",
     *     summary="Update User Profile",
     *     description="Update the details of a user profile by ID.",
     *     tags={"Restaurant Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User profile ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=4
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"restaurantId", "email"},
     *             properties={
     *                 @OA\Property(property="firstName", type="string", example="John"),
     *                 @OA\Property(property="lastName", type="string", example="Doe"),
     *                 @OA\Property(property="gender", type="string", example="Male"),
     *                 @OA\Property(property="restName", type="string", example="Doe's Restaurant"),
     *                 @OA\Property(property="phoneNumber", type="string", example="1234567890"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="pinCode", type="string", example="123456"),
     *                 @OA\Property(property="restaurantId", type="string", example="R1728231298"),
     *                 @OA\Property(property="identity", type="string", example="Passport"),
     *                 @OA\Property(property="identityNumber", type="string", example="P123456789"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Profile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Profile not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Failed to update profile")
     *         )
     *     )
     * )
     */

    public function updateProfile(Request $request, $id)
    {


        // Validate the incoming request data
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:10',
            'restName' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phoneNumber' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'pinCode' => 'nullable|string|max:10',
            'restaurantId' => 'required|string|max:20',
            'identity' => 'nullable|string|max:255',
            'identityNumber' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        // Find the profile by ID here
        $profile = UserProfile::find($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // Update the profile fields
        $profile->firstName = $validatedData['firstName'] ?? $profile->firstName;
        $profile->lastName = $validatedData['lastName'] ?? $profile->lastName;
        $profile->gender = $validatedData['gender'] ?? $profile->gender;
        $profile->restName = $validatedData['restName'] ?? $profile->restName;
        $profile->phoneNumber = $validatedData['phoneNumber'] ?? $profile->phoneNumber;
        $profile->address = $validatedData['address'] ?? $profile->address;
        $profile->pinCode = $validatedData['pinCode'] ?? $profile->pinCode;
        $profile->restaurantId = $validatedData['restaurantId'];
        $profile->identity = $validatedData['identity'] ?? $profile->identity;
        $profile->identityNumber = $validatedData['identityNumber'] ?? $profile->identityNumber;
        $profile->email = $validatedData['email'] ?? $profile->email;

        // Handle image upload if provided
        if ($request->hasFile('image')) {

            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $imagePath = public_path('profile_images');

            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0777, true);
            }
            if ($profile->image) {
                unlink($profile->image);
            }

            $request->file('image')->move($imagePath, $imageName);
            $publicImageUrl = 'profile_images/' . $imageName;

            $profile->image = $publicImageUrl;
        }

        // Save the updated profile
        try {
            $profile->save();

            $profile->image = url($profile->image);
            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $profile
            ], 200);
        } catch (\Exception $e) {

            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/restaurant/{id}/logo",
     *     summary="Get restaurant logo",
     *     description="Fetches the logo of a restaurant using its restaurant ID.",
     *     tags={"Restaurant"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID (String)",
     *         @OA\Schema(type="string", example="restaurant_123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="logo", type="string", example="https://example.com/storage/logos/logo.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant profile not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getLogo($id)
    {
        $profile = UserProfile::where('restaurantId', $id)->first();

        if (!$profile || !$profile->image) {
            return response()->json(['error' => 'Restaurant profile not found'], 404);
        }

        return response()->json(['logo' => env('APP_URL') . '/' . ltrim($profile->image, '/')]);
    }
}
