<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\OtpMail;
use App\Models\UserProfile;

/**
 * @OA\Info(title="Category API", version="1.0")
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Use a Bearer token to access this API",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth",
 * )
 * @OA\OpenApi(
 *     security={{"bearerAuth":{}}}
 * )
 */

class AuthController extends Controller
{


    /**
     * @OA\Post(
     *      path="/register",
     *      operationId="registerUser",
     *      tags={"Authentication"},
     *      summary="Register a new user",
     *      description="Register a new user and return JWT token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","role","password","password_confirmation"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *              @OA\Property(property="role", type="string", example="admin"),
     *              @OA\Property(property="password", type="string", format="password", example="secret"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="secret")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful registration",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", type="object"),
     *              @OA\Property(property="token", type="string")
     *          )
     *      ),
     *      @OA\Response(response=400, description="Validation error"),
     *      @OA\Response(response=500, description="Internal server error")
     * )
     */


    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'));
    }

    /**
     * @OA\Post(
     *      path="/login",
     *      operationId="loginUser",
     *      tags={"Authentication"},
     *      summary="Log in a user and send OTP",
     *      description="Log in a user with email and password, and send OTP for verification",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="secret")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OTP sent to email",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="OTP sent to your email")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Invalid credentials"),
     *      @OA\Response(response=500, description="Internal server error")
     * )
     */

     public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (! $token = auth()->attempt($credentials)) {
        return response()->json(['error' => 'Invalid Credentials'], 401);
    }

    // Generate OTP
    $otp = rand(100000, 999999); // A 6-digit OTP

    // Set OTP expiration time (current time + 5 minutes in GMT)
    $expireAt = now('Asia/Kolkata')->addMinutes(5);

    // Update the user record with the OTP and expiration time
    $user = auth()->user();
    $user->otp = $otp;
    $user->expire_at = $expireAt;
    $user->save();

    // Send OTP to the user's email
    Mail::to($user->email)->send(new OtpMail($otp));

    // Logout the user temporarily
    auth()->logout();

    return response()->json(['message' => 'OTP sent to your email']);
}



    /**
     * @OA\Post(
     *      path="/verify-otp",
     *      operationId="verifyOtp",
     *      tags={"Authentication"},
     *      summary="Verify OTP and log in user",
     *      description="Verify the OTP sent to the user's email and log them in",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"otp", "email"},
     *              @OA\Property(property="otp", type="string", example="123456"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OTP verified, user logged in",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Invalid OTP or email"),
     *      @OA\Response(response=404, description="User not found"),
     *      @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function verifyOtp(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'otp' => 'required|string',
        'email' => 'required|email',
    ]);

    // Fetch the user by email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Check if the OTP is correct and not expired
    $currentUtcTime = now(); // Get the current UTC time
    if ($user->otp !== $request->otp || $user->expire_at < $currentUtcTime) {
        return response()->json(['error' => 'Invalid or expired OTP'], 401);
    }

    // Generate a JWT token for the user
    $token = JWTAuth::fromUser($user);

    // Clear the OTP fields after successful verification
    $user->otp = null;
    $user->expire_at = null;
    $user->save();

    // Check if the user is an admin
    if ($user->role == 'admin') {
        // Return token, user ID, and restaurant ID in the response for admins

        $userProfile = UserProfile::where('restaurantId', $user->restaurantId)->first();
        return response()->json([
            'token' => $token,
            'user_id' => $user->id,
            'profile_image' => isset($userProfile->image) ? url($userProfile->image) : "https://placehold.co/100",
            'restaurant_id' => $user->restaurantId,
        ]);
    }

    // If the user is not an admin, return just the token in the response
    return response()->json(['token' => $token]);
}


/*
    Whenever i need the token i simply use

    $token = Auth::token;

*/


    /**
     * @OA\Post(
     *      path="/logout",
     *      operationId="logoutUser",
     *      tags={"Authentication"},
     *      summary="Log out user",
     *      description="Log out the authenticated user",
     *      @OA\Response(
     *          response=200,
     *          description="User logged out successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User successfully signed out")
     *          )
     *      ),
     *      @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * @OA\Get(
     *      path="/me",
     *      operationId="getAuthenticatedUser",
     *      tags={"Authentication"},
     *      summary="Get the authenticated user",
     *      description="Return details of the authenticated user",
     *      @OA\Response(
     *          response=200,
     *          description="Authenticated user details",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", type="object")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function me()
    {
        return response()->json(auth()->user());
    }
}
