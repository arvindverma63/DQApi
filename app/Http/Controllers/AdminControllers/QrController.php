<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\PngImageRenderer;
use BaconQrCode\Renderer\Image\ImageRendererInterface;

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
    $text = env('MOBILE_URL') . "/menu/?restaurantId=" . $validated['restaurantId'] . "&tableNo=" . $validated['tableNo'];

    // Create an instance of the PNG renderer
    $renderer = new PngImageRenderer(
        ImageRendererInterface::IMG_FORMAT_PNG, // Format of the image (PNG)
        200 // Size of the image
    );

    // Create an instance of the Writer
    $writer = new Writer($renderer);

    // Generate the QR code as a PNG image
    $qrCode = $writer->writeString($text);

    // Save the QR code as an image file in the 'public' disk
    $fileName = 'qrcodes/' . time() . '.png';
    Storage::disk('public')->put($fileName, $qrCode);

    // Get the public URL of the QR code
    $qrCodeUrl = env('APP_URL') . '/storage/' . $fileName;

    // Store the QR code data in the database
    DB::table('qr')->insert([
        'restaurantId' => $validated['restaurantId'],
        'qrImage' => $fileName, // Save the file name
        'qrCodeUrl' => $qrCodeUrl,
        'created_at' => now(),
        'updated_at' => now(),
        'tableNumber' => $validated['tableNo'],
    ]);

    // Return the QR code URL in the response
    return response()->json([
        'message' => 'QR code generated and stored successfully!',
        'qrCodeUrl' => $qrCodeUrl  // Return the final URL to the QR code image
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
