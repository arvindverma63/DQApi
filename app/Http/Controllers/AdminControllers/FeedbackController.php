<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * @OA\Post(
     *     path="/admin/feedback/add",
     *     tags={"Feedback"},
     *     summary="Add a new feedback entry",
     *     description="Allows an admin to add feedback.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customerId"},
     *             @OA\Property(property="feedback", type="string", example="Great service!"),
     *             @OA\Property(property="short", type="string", example="Service Feedback"),
     *             @OA\Property(property="customerId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Feedback successfully added",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback added successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
    public function addFeedback(Request $request)
    {
        $validated = $request->validate([
            'feedback' => 'nullable|string',
            'short' => 'nullable|string',
            'customerId' => 'required|integer',
        ]);

        Feedback::create($validated);

        return response()->json(['message' => 'Feedback added successfully.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/feedbacks/{id}",
     *     summary="Get all feedbacks",
     *     description="Retrieve all feedbacks for a specific restaurant using its restaurantId.",
     *     tags={"Feedbacks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The restaurant ID as a string",
     *         @OA\Schema(type="string", example="R1732246184")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="string", example="R1732246184"),
     *                 @OA\Property(property="feedback", type="string", example="Great food and service!"),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-01T10:15:30Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-01T12:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Restaurant not found")
     *         )
     *     )
     * )
     */
    public function getAllFeedbacks($id)
    {
        $response = Feedback::where('restaurantId', $id)->get();

        return response()->json($response);
    }
}
