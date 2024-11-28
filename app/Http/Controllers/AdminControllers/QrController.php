<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QrController extends Controller
{
    /**
     * Create a new QR code.
     */
     /**
     * @OA\Post(
     *     path="/qr/create",
     *     summary="Create a new QR code",
     *     tags={"QR Code"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="restaurantId", type="string", example="12345"),
     *             @OA\Property(property="tableNo", type="integer", example="12"),
     *
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code generated and stored successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function createQr(Request $request)
{
    // Validate the input
    $validated = $request->validate([
        'tableNo' => 'integer|required',
        'restaurantId' => 'string|required'
    ]);

    // Generate the text for the QR code, including the full URL
    $text = env('MOBILE_URL') . "/menu/?restaurantId=" . urlencode($validated['restaurantId']) . "&tableNo=" . urlencode($validated['tableNo']);

    // Generate the QR code as a Base64 string
    $qrCode = QrCode::format('png')
                    ->size(400)  // Size of the QR code
                    ->errorCorrection('H')  // Error correction level
                    ->generate($text);

    // Encode the QR code as Base64
    $base64QrCode = base64_encode($qrCode);

    // Optional: You can create a Data URL for the image (for easy embedding in HTML)
    $dataUrl = 'data:image/png;base64,' . $base64QrCode;

    // Store the Base64 encoded QR code in the database if needed
    DB::table('qr')->insert([
        'restaurantId' => $validated['restaurantId'],
        'qrImageBase64' => $base64QrCode,  // Save the Base64 string to the database
        'created_at' => now(),
        'updated_at' => now(),
        'tableNumber' => $validated['tableNo'],
    ]);

    // Return the Base64 string or Data URL in the response
    return response()->json([
        'message' => 'QR code generated and stored successfully!',
        'qrCodeBase64' => $base64QrCode,  // Base64 string
        'qrCodeDataUrl' => $dataUrl,  // Data URL for embedding in HTML (optional)
    ], 200);
}



   /**
     * Retrieve a QR code by ID.
     */
    /**
     * @OA\Get(
     *     path="/qr/{id}",
     *     summary="Get a QR code by ID",
     *     tags={"QR Code"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QR code not found"
     *     )
     * )
     */
    public function getQr($id)
    {
        $qr = DB::table('qr')->where('restaurantId',$id)->get();

        if (!$qr) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        return response()->json($qr);
    }

    /**
     * Update a QR code by ID.
     */
    /**
     * @OA\Put(
     *     path="/qr/update/{id}",
     *     summary="Update a QR code by ID",
     *     tags={"QR Code"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="restaurantId", type="string", example="12345"),
     *             @OA\Property(property="tableNo", type="integer", example="12")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QR code not found"
     *     )
     * )
     */
    public function updateQr(Request $request, $id)
    {
        // Validate the input
        $validated = $request->validate([
            'tableNo' => 'integer|nullable',
            'restaurantId' => 'string|nullable'
        ]);

        // Find the QR record
        $qr = DB::table('qr')->find($id);
        if (!$qr) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        // Update the QR code in the database
        DB::table('qr')
            ->where('id', $id)
            ->update(array_merge($validated, ['updated_at' => now()]));

        return response()->json(['message' => 'QR code updated successfully!']);
    }

    /**
     * Delete a QR code by ID.
     */

    /**
     * @OA\Delete(
     *     path="/qr/delete/{id}",
     *     summary="Delete a QR code by ID",
     *     tags={"QR Code"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="QR code not found"
     *     )
     * )
     */
    public function deleteQr($id)
    {
        // Find and delete the QR record
        $qr = DB::table('qr')->find($id);
        if (!$qr) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        // Delete the QR code image from storage
        Storage::disk('public')->delete($qr->qrImage);

        // Delete the QR code from the database
        DB::table('qr')->where('id', $id)->delete();

        return response()->json(['message' => 'QR code deleted successfully!']);
    }
}
