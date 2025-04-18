<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\DeliveryManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class DeliveryManagementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/deliveries",
     *     summary="Get all delivery schedules",
     *     tags={"Delivery Time Management"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="restaurantId", type="string", example="REST123"),
     *                     @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                     @OA\Property(property="end_time", type="string", example="17:00:00"),
     *                     @OA\Property(property="delivery_status", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", example="2025-04-18 10:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-04-18 10:00:00")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $deliveries = DeliveryManagement::all();
        return response()->json(['data' => $deliveries], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/deliveries",
     *     summary="Create a new delivery schedule",
     *     tags={"Delivery Time Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurantId","start_time","end_time","delivery_status"},
     *             @OA\Property(property="restaurantId", type="string", maxLength=20, example="REST123"),
     *             @OA\Property(property="start_time", type="string", maxLength=30, example="09:00:00"),
     *             @OA\Property(property="end_time", type="string", maxLength=30, example="17:00:00"),
     *             @OA\Property(property="delivery_status", type="string", enum={"0","1"}, example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Delivery created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="string", example="REST123"),
     *                 @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                 @OA\Property(property="end_time", type="string", example="17:00:00"),
     *                 @OA\Property(property="delivery_status", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-04-18 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-04-18 10:00:00")
     *             ),
     *             @OA\Property(property="message", type="string", example="Delivery created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurantId' => 'required|string|max:20',
            'start_time' => 'required|string|max:30',
            'end_time' => 'required|string|max:30',
            'delivery_status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $delivery = DeliveryManagement::create([
            'restaurantId' => $request->restaurantId,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'delivery_status' => $request->delivery_status,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString()
        ]);

        return response()->json(['data' => $delivery, 'message' => 'Delivery created successfully'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/deliveries/{id}",
     *     summary="Get a specific delivery schedule",
     *     tags={"Delivery Time Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="string", example="REST123"),
     *                 @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                 @OA\Property(property="end_time", type="string", example="17:00:00"),
     *                 @OA\Property(property="delivery_status", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-04-18 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-04-18 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Delivery not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Delivery not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $delivery = DeliveryManagement::find($id);

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], 404);
        }

        return response()->json(['data' => $delivery], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/deliveries/{id}",
     *     summary="Update a delivery schedule",
     *     tags={"Delivery Time Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="restaurantId", type="string", maxLength=20, example="REST123"),
     *             @OA\Property(property="start_time", type="string", maxLength=30, example="09:00:00"),
     *             @OA\Property(property="end_time", type="string", maxLength=30, example="17:00:00"),
     *             @OA\Property(property="delivery_status", type="string", enum={"0","1"}, example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="string", example="REST123"),
     *                 @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                 @OA\Property(property="end_time", type="string", example="17:00:00"),
     *                 @OA\Property(property="delivery_status", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-04-18 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-04-18 10:00:00")
     *             ),
     *             @OA\Property(property="message", type="string", example="Delivery updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Delivery not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Delivery not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $delivery = DeliveryManagement::find($id);

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'restaurantId' => 'sometimes|required|string|max:20',
            'start_time' => 'sometimes|required|string|max:30',
            'end_time' => 'sometimes|required|string|max:30',
            'delivery_status' => 'sometimes|required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $delivery->update(array_merge(
            $request->only(['restaurantId', 'start_time', 'end_time', 'delivery_status']),
            ['updated_at' => now()->toDateTimeString()]
        ));

        return response()->json(['data' => $delivery, 'message' => 'Delivery updated successfully'], 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/deliveries/{id}/status",
     *     summary="Update delivery status only",
     *     tags={"Delivery Time Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"delivery_status"},
     *             @OA\Property(property="delivery_status", type="string", enum={"0","1"}, example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="restaurantId", type="string", example="REST123"),
     *                 @OA\Property(property="start_time", type="string", example="09:00:00"),
     *                 @OA\Property(property="end_time", type="string", example="17:00:00"),
     *                 @OA\Property(property="delivery_status", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-04-18 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-04-18 10:00:00")
     *             ),
     *             @OA\Property(property="message", type="string", example="Delivery status updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Delivery not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Delivery not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $delivery = DeliveryManagement::find($id);

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'delivery_status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $delivery->update([
            'delivery_status' => $request->delivery_status,
            'updated_at' => now()->toDateTimeString()
        ]);

        return response()->json(['data' => $delivery, 'message' => 'Delivery status updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/deliveries/{id}",
     *     summary="Delete a delivery schedule",
     *     tags={"Delivery Time Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Delivery deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Delivery not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Delivery not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $delivery = DeliveryManagement::find($id);

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found'], 404);
        }

        $delivery->delete();
        return response()->json(['message' => 'Delivery deleted successfully'], 200);
    }
}
