<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;


class FirebaseNotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * @OA\Post(
     *     path="/api/send-notification",
     *     summary="Send a push notification",
     *     tags={"Firebase Notifications"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_token", "title", "body"},
     *             @OA\Property(property="device_token", type="string", description="Device token of the recipient"),
     *             @OA\Property(property="title", type="string", description="Notification title"),
     *             @OA\Property(property="body", type="string", description="Notification body"),
     *             @OA\Property(property="data", type="object", description="Additional data payload")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Notification sent successfully"),
     *     @OA\Response(response=400, description="Bad Request - Invalid input data"),
     * )
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $deviceToken = $request->input('device_token');
        $title = $request->input('title');
        $body = $request->input('body');
        $data = $request->input('data', []);

        $response = $this->firebaseService->sendNotification($deviceToken, $title, $body, $data);

        return response()->json([
            'message' => 'Notification sent successfully',
            'response' => $response,
        ]);
    }
}
