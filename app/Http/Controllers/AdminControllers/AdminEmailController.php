<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Mail\BulkEmail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Mail;

class AdminEmailController extends Controller
{
    /**
     * @OA\Post(
     *     path="/admin/send-bulk-email",
     *     tags={"Admin Email"},
     *     summary="Send bulk email to customers of a specific restaurant",
     *     description="Sends queued bulk emails to all customers associated with the given restaurant ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId", "subject", "title", "body"},
     *             @OA\Property(property="restaurantId", type="integer", example=1),
     *             @OA\Property(property="subject", type="string", example="Hello from Laravel!"),
     *             @OA\Property(property="title", type="string", example="Bulk Email Notice"),
     *             @OA\Property(property="body", type="string", example="This is a bulk email sent without migrations using queues.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="mail sent successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function buikMain(Request $request)
    {
        $details = [
            'subject' => $request->subject,
            'title' => $request->title,
            'body' => $request->body,
        ];

        $userEmails = Customer::where('restaurantId', $request->restaurantId)
                        ->whereNot('email', '')->get();

        foreach ($userEmails as $email) {
            Mail::to($email)->queue(new BulkEmail($details));
        }

        return response()->json(['success' => 'mail sent successfully']);
    }
}
