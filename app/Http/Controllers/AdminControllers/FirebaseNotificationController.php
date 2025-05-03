<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\FirebaseException; // Import the exception class
use Illuminate\Support\Facades\Log; // Import the Log facade

class FirebaseNotificationController extends Controller
{

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
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path(config('services.firebase.credentials')));

        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
        try {
            // Log the start of the notification process
            Log::info('Sending notification', [
                'device_token' => $deviceToken,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            // Send notification
            $response = $this->messaging->send($message);

            // Log the success response from Firebase
            Log::info('Notification sent successfully', [
                'response' => $response,
            ]);

            // Return success response
            return response()->json([
                'message' => 'Notification sent successfully',
                'response' => $response,
            ]);

        } catch (FirebaseException $e) {
            // Log the Firebase error
            Log::error('Firebase error while sending notification', [
                'error' => $e->getMessage(),
                'device_token' => $deviceToken,
            ]);

            // Catch any Firebase-specific exceptions
            return response()->json([
                'error' => 'Firebase Error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Log the general error
            Log::error('Error while sending notification', [
                'error' => $e->getMessage(),
                'device_token' => $deviceToken,
            ]);

            // Catch any other general exceptions
            return response()->json([
                'error' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
