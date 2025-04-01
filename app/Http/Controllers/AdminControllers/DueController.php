<?php

namespace App\Http\Controllers\AdminControllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DueTransactions;
use App\Models\Transaction;

/**
 * @OA\Tag(
 *     name="Dues",
 *     description="Operations related to due transactions"
 * )
 */
class DueController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dues/byRestaurantId/{restaurantId}",
     *     tags={"Dues"},
     *     summary="Get a specific due record according to RestaurantId",
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="ID of the restaurant to get dues for",
     *         @OA\Schema(type="string", example="R23423423")
     *     ),
     *     @OA\Response(response=200, description="List of dues")
     * )
     */

    public function index($restaurantId)
    {
        $dueRecords = DueTransactions::where('restaurantId', $restaurantId)->get();
        $data = [];


            $data[] = [
                'due_details' => $dueRecords
            ];

        return response()->json($data);
    }

    /**
     * @OA\Post(
     *     path="/dues",
     *     tags={"Dues"},
     *     summary="Create a new due record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id", "total", "status"},
     *             @OA\Property(property="customer_id", type="integer", example=123),
     *             @OA\Property(property="total", type="number", format="float", example=1000.500),
     *             @OA\Property(property="restaurantId", type="string", format="string", example="R2342342"),
     *             @OA\Property(property="status", type="string", enum={"paid", "unpaid"}, example="unpaid")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Due record created")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'total' => 'required|numeric',
            'restaurantId' => 'required',
            'status' => 'required|in:paid,unpaid',
        ]);

        $due = DueTransactions::create($request->all());

        return response()->json($due, 201);
    }

    /**
     * @OA\Get(
     *     path="/dues/{id}",
     *     tags={"Dues"},
     *     summary="Get a specific due record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the due record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Due record found"),
     *     @OA\Response(response=404, description="Due record not found")
     * )
     */
    public function show($id)
    {
        $due = DueTransactions::find($id);

        if (!$due) {
            return response()->json(['error' => 'Due record not found'], 404);
        }

        return response()->json([
            'due_details' => $due
        ]);
    }


    /**
     * @OA\Put(
     *     path="/dues/{id}",
     *     tags={"Dues"},
     *     summary="Update a specific due record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the due record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_id", type="integer", example=123),
     *             @OA\Property(property="total", type="number", format="float", example=1000.500),
     *             @OA\Property(property="status", type="string", enum={"paid", "unpaid"}, example="paid")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Due record updated"),
     *     @OA\Response(response=404, description="Due record not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $due = DueTransactions::find($id);
        if (!$due) {
            return response()->json(['error' => 'Due record not found'], 404);
        }

        $request->validate([
            'status' => 'in:paid,unpaid',
        ]);

        $due->update($request->all());

        return response()->json($due);
    }

    /**
     * @OA\Delete(
     *     path="/dues/{id}",
     *     tags={"Dues"},
     *     summary="Delete a specific due record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the due record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Due record deleted"),
     *     @OA\Response(response=404, description="Due record not found")
     * )
     */
    public function destroy($id)
    {
        $due = DueTransactions::find($id);
        if (!$due) {
            return response()->json(['error' => 'Due record not found'], 404);
        }

        $due->delete();

        return response()->json(['message' => 'Due record deleted']);
    }
}
