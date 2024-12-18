<?php
namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/send-invoice-email",
     *     tags={"Admin - Invoice"},
     *     summary="Send invoice email",
     *     description="Send an HTML invoice to the specified email address.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"htmlContent", "email"},
     *             @OA\Property(property="htmlContent", type="string", description="HTML content of the invoice"),
     *             @OA\Property(property="email", type="string", format="email", description="Recipient's email address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invoice sent successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to send invoice."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function sendInvoiceEmail(Request $request)
    {
        $request->validate([
            'htmlContent' => 'required|string',
            'email' => 'required|email',
        ]);

        $htmlContent = $request->input('htmlContent');
        $recipientEmail = $request->input('email');

        try {
            Mail::send([], [], function ($message) use ($htmlContent, $recipientEmail) {
                $message->to($recipientEmail)
                        ->subject('Invoice')
                        ->setBody($htmlContent, 'text/html');
            });

            return response()->json(['success' => true, 'message' => 'Invoice sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send invoice.', 'error' => $e->getMessage()]);
        }
    }
}
