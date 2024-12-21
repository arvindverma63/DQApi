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
    public function addFeedback(Request $request){
        $validated = $request->validate([
            'feedback' => 'nullable|string',
            'short' => 'nullable|string',
            'customerId' => 'required|integer',
        ]);

        Feedback::create($validated);

        return response()->json(['message' => 'Feedback added successfully.'], 201);
    }
}
