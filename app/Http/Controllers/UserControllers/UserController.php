<?php

namespace App\Http\Controllers\UserControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/user/profile",
     *     tags={"User"},
     *     summary="Get user profile",
     *     description="Returns the user profile data",
     *     operationId="getUserProfile",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User Profile Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function profile()
    {
        return response()->json(['message' => 'User Profile Data'], 200);
    }

    /**
     * @OA\Get(
     *     path="/user/dashboard",
     *     tags={"User"},
     *     summary="Get user dashboard",
     *     description="Returns the user dashboard data",
     *     operationId="getUserDashboard",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User Dashboard Data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function dashboard()
    {
        return response()->json(['message' => 'User Dashboard Data'], 200);
    }
}
