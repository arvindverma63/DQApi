<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

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
 *     description="Operations related to restaurant user profiles"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Profile",
 *     type="object",
 *     required={"restaurantId", "email"},
 *     @OA\Property(property="id", type="integer", description="Profile ID", example=1),
 *     @OA\Property(property="firstName", type="string", description="User's first name", nullable=true, example="John"),
 *     @OA\Property(property="lastName", type="string", description="User's last name", nullable=true, example="Doe"),
 *     @OA\Property(property="gender", type="string", description="User's gender", nullable=true, enum={"male", "female", "other"}, example="male"),
 *     @OA\Property(property="restName", type="string", description="Restaurant name", nullable=true, example="Doe's Restaurant"),
 *     @OA\Property(property="image", type="string", description="Profile image URL", nullable=true, example="https://example.com/profile_images/image.jpg"),
 *     @OA\Property(property="phoneNumber", type="string", description="User's phone number", nullable=true, example="1234567890"),
 *     @OA\Property(property="address", type="string", description="User's address", nullable=true, example="123 Main St"),
 *     @OA\Property(property="pinCode", type="string", description="User's postal code", nullable=true, example="12345"),
 *     @OA\Property(property="restaurantId", type="string", description="Restaurant identifier", example="R1728231298"),
 *     @OA\Property(property="identity", type="string", description="Identity type", nullable=true, example="Passport"),
 *     @OA\Property(property="identityNumber", type="string", description="Identity number", nullable=true, example="P123456789"),
 *     @OA\Property(property="email", type="string", description="User's email address", example="john.doe@example.com")
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
     *         @OA\Schema(type="string", example="R1728231298")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Profile")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Profile not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred")
     *         )
     *     )
     * )
     */
    public function getProfile($id)
    {
        try {
            $profile = UserProfile::where('restaurantId', $id)->firstOrFail();
            $profile->image = $profile->image ? url($profile->image) : null;

            return response()->json($profile, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Profile not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/profile/{id}",
     *     summary="Update User Profile",
     *     tags={"Restaurant Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Profile ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "email"},
     *             @OA\Property(property="firstName", type="string", example="John"),
     *             @OA\Property(property="lastName", type="string", example="Doe"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
     *             @OA\Property(property="restName", type="string", example="Doe's Restaurant"),
     *             @OA\Property(property="phoneNumber", type="string", example="1234567890"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="pinCode", type="string", example="12345"),
     *             @OA\Property(property="restaurantId", type="string", example="R1728231298"),
     *             @OA\Property(property="identity", type="string", example="Passport"),
     *             @OA\Property(property="identityNumber", type="string", example="P123456789"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Profile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="fieldName", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to update profile"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request, $id)
    {
        try {
            $profile = UserProfile::findOrFail($id);

            $validator = Validator::make($request->all(), $this->getValidationRules());

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            $profile->update(array_filter($validatedData, function ($value) {
                return !is_null($value);
            }));

            $profile->image = $profile->image ? url($profile->image) : null;

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $profile
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Profile not found'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/profile/{id}/image",
     *     summary="Upload Profile Image",
     *     tags={"Restaurant Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Profile ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile image file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image uploaded successfully"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/profile_images/image.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="image", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to upload image"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function uploadImage(Request $request, $id)
    {
        try {
            $profile = UserProfile::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $imagePath = public_path('profile_images');

            // Create directory if it doesn't exist
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0755, true);
            }

            // Delete old image if exists
            if ($profile->image && file_exists(public_path($profile->image))) {
                unlink(public_path($profile->image));
            }

            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move($imagePath, $imageName);

            $relativePath = 'profile_images/' . $imageName;
            $profile->image = $relativePath;
            $profile->save();

            $imageUrl = url($relativePath);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'image_url' => $imageUrl
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Profile not found'], 404);
        } catch (Exception $e) {
            if (isset($relativePath) && file_exists(public_path($relativePath))) {
                unlink(public_path($relativePath));
            }
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/restaurant/{id}/logo",
     *     summary="Get Restaurant Logo",
     *     tags={"Restaurant Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(type="string", example="R1728231298")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logo retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="logo",
     *                 type="string",
     *                 example="https://example.com/profile_images/logo.jpg"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logo or profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Logo not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred")
     *         )
     *     )
     * )
     */
    public function getLogo($id)
    {
        try {
            $profile = UserProfile::where('restaurantId', $id)->firstOrFail();

            if (!$profile->image) {
                return response()->json(['error' => 'Logo not found'], 404);
            }

            $logoUrl = url($profile->image);

            return response()->json(['logo' => $logoUrl], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Restaurant profile not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    private function getValidationRules(): array
    {
        return [
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other|max:10',
            'restName' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|string|regex:/^[0-9]{10,15}$/',
            'address' => 'nullable|string|max:255',
            'pinCode' => 'nullable|string|regex:/^[0-9]{5,10}$/',
            'restaurantId' => 'required|string|max:20',
            'identity' => 'nullable|string|max:255',
            'identityNumber' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ];
    }
}
