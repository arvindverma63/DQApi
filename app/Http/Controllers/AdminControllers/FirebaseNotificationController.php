<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

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
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->send($message);
    }
}
