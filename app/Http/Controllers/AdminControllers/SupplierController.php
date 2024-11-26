<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller; // Import the base Controller class
/**
 * @OA\Schema(
 *     schema="Supplier",
 *     type="object",
 *     title="Supplier",
 *     description="Supplier schema",
 *     @OA\Property(property="id", type="integer", example=1, description="Supplier ID"),
 *     @OA\Property(property="supplierName", type="string", example="Supplier One", description="Supplier Name"),
 *     @OA\Property(property="email", type="string", example="supplier@example.com", description="Supplier Email"),
 *     @OA\Property(property="phoneNumber", type="string", example="1234567890", description="Supplier Phone Number"),
 *     @OA\Property(property="rawItem", type="string", example="Raw Materials", description="Raw materials provided by supplier"),
 *     @OA\Property(property="restaurantId", type="string", example="R123456", description="Restaurant ID associated with supplier"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-11T13:55:52.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-12T13:55:52.000000Z"),
 * )
 */


class SupplierController extends Controller
{
     /**
 * @OA\Get(
 *     path="/suppliers",
 *     summary="Get all suppliers for a specific restaurant",
 *     description="Retrieve a list of all suppliers filtered by restaurantId",
 *     tags={"Suppliers"},
 *     @OA\Parameter(
 *         name="restaurantId",
 *         in="query",
 *         description="The restaurant ID to filter suppliers",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="A list of suppliers",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Supplier")
 *         )
 *     ),
 *     @OA\Response(response=404, description="No suppliers found for this restaurant"),
 *     @OA\Response(response=400, description="Invalid input")
 * )
 */
public function getSuppliers(Request $request)
{
    // Validate the restaurantId query parameter
    $request->validate([
        'restaurantId' => 'required|string',
    ]);

    // Fetch suppliers by restaurantId
    $suppliers = Supplier::where('restaurantId', $request->restaurantId)->get();


    // Return the list of suppliers
    return response()->json(['data' => $suppliers, 'message' => 'Suppliers retrieved successfully'], 200);
}


    /**
     * @OA\Get(
     *     path="/suppliers/{id}",
     *     summary="Get a specific supplier",
     *     description="Retrieve a supplier by their ID",
     *     tags={"Suppliers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The supplier ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier details",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(response=404, description="Supplier not found")
     * )
     */
    public function getSupplier($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        return response()->json(['data' => $supplier, 'message' => 'Supplier retrieved successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/suppliers",
     *     summary="Create a new supplier",
     *     description="Add a new supplier",
     *     tags={"Suppliers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function createSupplier(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'supplierName' => 'required|string|max:255',
            'email' => 'required|email',
            'phoneNumber' => 'required|string|max:15',
            'rawItem' => 'required|string|max:255',
            'restaurantId' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create supplier
        $supplier = Supplier::create($request->all());

        return response()->json(['data' => $supplier, 'message' => 'Supplier created successfully'], 201);
    }

    /**
     * @OA\Put(
     *     path="/suppliers/{id}",
     *     summary="Update a supplier",
     *     description="Update a supplier's details",
     *     tags={"Suppliers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The supplier ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="Supplier not found")
     * )
     */
    public function updateSupplier(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'supplierName' => 'string|max:255',
            'email' => 'email',
            'phoneNumber' => 'string|max:15',
            'rawItem' => 'string|max:255',
            'restaurantId' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->update($request->all());

        return response()->json(['data' => $supplier, 'message' => 'Supplier updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/suppliers/{id}",
     *     summary="Delete a supplier",
     *     description="Remove a supplier by their ID",
     *     tags={"Suppliers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The supplier ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier deleted successfully",
     *         @OA\JsonContent(type="string", example="Supplier deleted successfully")
     *     ),
     *     @OA\Response(response=404, description="Supplier not found")
     * )
     */
    public function deleteSupplier($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully'], 200);
    }
}
