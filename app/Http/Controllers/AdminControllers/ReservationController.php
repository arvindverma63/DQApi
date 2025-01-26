<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Log;

/**
 * @OA\Info(
 *     title="Reservation API",
 *     version="1.0.0",
 *     description="API documentation for Reservation management"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     title="Reservation",
 *     required={"restaurantId", "startTime", "endTime", "customerId", "payment", "advance", "created_at", "updated_at"},
 *     @OA\Property(property="restaurantId", type="string", description="Restaurant ID"),
 *     @OA\Property(property="startTime", type="string", description="Start time of the reservation"),
 *     @OA\Property(property="endTime", type="string", description="End time of the reservation"),
 *     @OA\Property(property="customerId", type="integer", description="Customer ID"),
 *     @OA\Property(property="payment", type="number", format="float", description="Payment amount"),
 *     @OA\Property(property="advance", type="number", format="float", description="Advance amount"),
 *     @OA\Property(property="notes", type="string", description="Additional notes"),
 * @OA\Property(property="tableNumber", type="string", description="add tables"),
 * )
 */

class ReservationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/reservations/AllByRestaurantId/{id}",
     *     tags={"Reservations"},
     * *     summary="Get a reservation by restaurantID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reservations",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reservation"))
     *     )
     * )
     */
    public function index($id)
    {
        // Fetch all reservations for the given restaurant
        $reservations = Reservation::where('restaurantId', $id)->get();

        // Get all unique customer IDs from the reservations
        $customerIds = $reservations->pluck('customerId')->unique();

        // Fetch customer details in one query
        $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');

        $data = $reservations->map(function ($reservation) use ($customers) {
            $customer = $customers->get($reservation->customerId);

            return [
                'customerName' => $customer->name ?? null,
                'customerPhoneNumber' => $customer->phoneNumber ?? null,
                'customerAddress' => $customer->address ?? null,
                'reservationDetails' => $reservation
            ];
        });

        return response()->json($data);
    }



    /**
     * @OA\Post(
     *     path="/reservations",
     *     tags={"Reservations"},
     *     summary="Create a new reservation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurantId' => 'required|string|max:255',
            'startTime' => 'required|string|max:30',
            'endTime' => 'required|string|max:30',
            'customerId' => 'required|integer',
            'payment' => 'required|numeric',
            'advance' => 'required|numeric',
            'notes' => 'nullable|string',
            'tableNumber' => 'nullable'
        ]);

        $reservation = Reservation::create($validated);

        return response()->json(['message' => 'Reservation created successfully', 'reservation' => $reservation], 201);
    }

    /**
     * @OA\Get(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Get a reservation by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation details",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     )
     * )
     */
    public function show($id)
    {
        // Fetch all reservations for the given restaurant
        $reservations = Reservation::find($id);

        // Get all unique customer IDs from the reservations
        $customerIds = $reservations->pluck('customerId')->unique();

        // Fetch customer details in one query
        $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');

        $data = $reservations->map(function ($reservation) use ($customers) {
            $customer = $customers->get($reservation->customerId);

            return [
                'customerName' => $customer->name ?? null,
                'customerPhoneNumber' => $customer->phoneNumber ?? null,
                'customerAddress' => $customer->address ?? null,
                'reservationDetails' => $reservation
            ];
        });

        return response()->json($data);
    }

    /**
     * @OA\Put(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Update a reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $validated = $request->validate([
            'restaurantId' => 'required|string|max:255',
            'startTime' => 'required|string|max:30',
            'endTime' => 'required|string|max:30',
            'customerId' => 'required|integer',
            'payment' => 'required|numeric',
            'advance' => 'required|numeric',
            'notes' => 'nullable|string',
            'tableNumber' => 'nullable',
        ]);

        $reservation->update($validated);

        return response()->json(['message' => 'Reservation updated successfully', 'reservation' => $reservation]);
    }

    /**
     * @OA\Delete(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Delete a reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservation deleted successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted successfully']);
    }
}
