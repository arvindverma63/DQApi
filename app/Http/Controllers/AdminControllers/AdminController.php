<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/check-permission/{id}",
     *     operationId="checkUserPermission",
     *     tags={"Restaurant Profile"},
     *     summary="Check user permission by restaurant ID",
     *     description="Returns the permission of a user profile by restaurant ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Restaurant ID of the user",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission found",
     *         @OA\JsonContent(
     *             @OA\Property(property="permission", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="user not found")
     *         )
     *     )
     * )
     */
    public function checkPermission($id)
    {
        $user = UserProfile::where('restaurantId', $id)->first();

        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        }

        return response()->json([
            'permission' => $user->permission
        ]);
    }
    /**
     * @OA\Put(
     *     path="/admin/update-permission",
     *     operationId="updateUserPermission",
     *     tags={"Restaurant Profile"},
     *     summary="Update user permission by restaurant ID",
     *     description="Updates the permission of a user profile using restaurant ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "permission"},
     *             @OA\Property(property="restaurantId", type="string", example="resto123"),
     *             @OA\Property(property="permission", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="user not found")
     *         )
     *     )
     * )
     */
    public function updatePermission(Request $request)
    {
        $user = UserProfile::where('restaurantId', $request->restaurantId)->first();

        if ($user) {
            $user->permission = $request->permission;
            $user->save();
            return response()->json(['message' => 'updated successfully']);
        }

        return response()->json(['error' => 'user not found'], 404);
    }
}
