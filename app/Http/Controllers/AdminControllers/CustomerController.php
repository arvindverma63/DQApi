<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Log;

/**
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *     @OA\Property(property="phoneNumber", type="string", example="1234567890"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="restaurantId", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CustomerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/customer",
     *     summary="Create a new customer",
     *     description="Creates a new customer with the provided details",
     *     tags={"Customer"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="phoneNumber", type="string", example="1234567890"),
     *             @OA\Property(property="address", type="string", example="123 Main St, City, Country"),
     *             @OA\Property(property="restaurantId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */

    public function createCustomer(Request $request)
    {
        try {
            $response = Customer::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'phoneNumber' => $request['phoneNumber'],
                'address' => $request['address'],
                'restaurantId' => $request['restaurantId']
            ]);
            return response()->json(['data' => $response]);
        } catch (Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    /**
     * @OA\Get(
     *     path="/customer/{id}",
     *     summary="Get Customer by Restaurant ID",
     *     description="Retrieve customer data by the provided restaurant ID.",
     *     operationId="getCustomerByRestaurantId",
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the restaurant to fetch customer data for",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of customer data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object", example={"id": 1, "name": "John Doe", "email": "johndoe@example.com"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer data not found for the given restaurant ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Customer data not found")
     *         )
     *     )
     * )
     */
    public function getCustomer($id)
    {
        $data = Customer::where('restaurantId', $id)->get();

        if ($data) {
            return response()->json($data);
        } else {
            return response()->json(['error' => 'Customer data not found'], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/customer/{id}",
     *     summary="Delete Customer by ID",
     *     description="Delete customer data by the provided ID.",
     *     operationId="deleteCustomer",
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer successfully deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Customer deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found for the given ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Customer not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to delete customer")
     *         )
     *     )
     * )
     */
    public function deleteCustomer($id)
    {
        try {
            // Find the customer by ID
            $customer = Customer::find($id);

            // Check if the customer exists
            if (!$customer) {
                return response()->json([
                    'error' => 'Customer not found'
                ], 404);
            }

            // Delete the customer
            $customer->delete();

            // Return success response
            return response()->json([
                'message' => 'Customer deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to delete customer:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            // Return error response
            return response()->json([
                'error' => 'Failed to delete customer'
            ], 500);
        }
    }
}
