<?php

namespace App\Http\Controllers\SuperAdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile; // Import UserProfile model
use JWTAuth; // Import JWTAuth
use Illuminate\Support\Facades\Hash;

/**
 * This controller contains the endpoints for user management.
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/super-admin/add-restaurant",
     *     tags={"Users For Admin"},
     *     summary="Create a new restaurant user",
     *     description="Creates a new user with restaurant role and profile.",
     *     operationId="addRestaurant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","role","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="role", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", format="password", example="secret"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function addRestaurant(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $restaurant_id = 'R' . time();

        // Create new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'password' => bcrypt($validatedData['password']),
            'restaurantId' => $restaurant_id
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Create a profile for the user
        $user_profile = UserProfile::create([
            'email' => $user->email,
            'userId' => $user->id,
            'restaurantId' => $restaurant_id
        ]);

        return response()->json([
            'data' => $user_profile,
            'message' => 'User created successfully',
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/super-admin/users",
     *     tags={"Users For Admin"},
     *     summary="Retrieve all users",
     *     description="Get a list of all users and their profiles.",
     *     operationId="getAllUsers",
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getAllUsers()
    {
        $users = User::where('role','admin')->with('userProfile')->get();
        return response()->json($users, 200);
    }

    /**
     * @OA\Get(
     *     path="/super-admin/users/{id}",
     *     tags={"Users For Admin"},
     *     summary="Retrieve a specific user",
     *     description="Get details of a specific user and their profile.",
     *     operationId="getUser",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUser($id)
    {
        $user = User::with('userProfile')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['data' => $user], 200);
    }

    /**
     * @OA\Put(
     *     path="/super-admin/users/{id}",
     *     tags={"Users For Admin"},
     *     summary="Update a user",
     *     description="Update details of a specific user.",
     *     operationId="updateUser",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="role", type="string", example="restaurant"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="User updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function updateUser(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|required|string',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update([
            'name' => $validatedData['name'] ?? $user->name,
            'email' => $validatedData['email'] ?? $user->email,
            'role' => $validatedData['role'] ?? $user->role,
            'password' => isset($validatedData['password']) ? Hash::make($validatedData['password']) : $user->password,
        ]);

        return response()->json(['data' => $user, 'message' => 'User updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/super-admin/users/{id}",
     *     tags={"Users For Admin"},
     *     summary="Soft delete a user",
     *     description="Soft delete a user by setting their deleted_at field.",
     *     operationId="deleteUser",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete(); // Soft delete

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
