<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillOperator;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Bill Operators",
 *     description="APIs for managing bill operators"
 * )
 */

/**
 * @OA\Schema(
 *     schema="BillOperator",
 *     type="object",
 *     title="Bill Operator",
 *     required={"restaurantId", "operator"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="restaurantId", type="integer", example=101),
 *     @OA\Property(property="operator", type="string", example="John Doe"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class BillOperatorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bill-operators",
     *     tags={"Bill Operators"},
     *     summary="List all operators for the authenticated restaurant",
     *     @OA\Response(
     *         response=200,
     *         description="List of operators",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/BillOperator"))
     *     )
     * )
     */
    public function index()
    {
        $restaurantId = Auth::user()->restaurantId;
        $operators = BillOperator::where('restaurantId', $restaurantId)->get();
        return response()->json($operators);
    }

    /**
     * @OA\Post(
     *     path="/bill-operators",
     *     tags={"Bill Operators"},
     *     summary="Create a new operator",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"operator"},
     *             @OA\Property(property="operator", type="string", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Operator created",
     *         @OA\JsonContent(ref="#/components/schemas/BillOperator")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'operator' => 'required|string|max:255',
        ]);

        $billOperator = BillOperator::create([
            'restaurantId' => Auth::user()->restaurantId,
            'operator' => $request->operator,
        ]);

        return response()->json(['message' => 'Operator created successfully', 'data' => $billOperator], 201);
    }

    /**
     * @OA\Get(
     *     path="/bill-operators/{id}",
     *     tags={"Bill Operators"},
     *     summary="Get a specific operator",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Operator ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operator details",
     *         @OA\JsonContent(ref="#/components/schemas/BillOperator")
     *     )
     * )
     */
    public function show($id)
    {
        $restaurantId = Auth::user()->restaurantId;
        $operator = BillOperator::where('restaurantId', $restaurantId)->findOrFail($id);

        return response()->json($operator);
    }

    /**
     * @OA\Put(
     *     path="/bill-operators/{id}",
     *     tags={"Bill Operators"},
     *     summary="Update an operator",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Operator ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"operator"},
     *             @OA\Property(property="operator", type="string", example="Updated Operator Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operator updated",
     *         @OA\JsonContent(ref="#/components/schemas/BillOperator")
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'operator' => 'required|string|max:255',
        ]);

        $restaurantId = Auth::user()->restaurantId;
        $billOperator = BillOperator::where('restaurantId', $restaurantId)->findOrFail($id);
        $billOperator->update([
            'operator' => $request->operator,
        ]);

        return response()->json(['message' => 'Operator updated successfully', 'data' => $billOperator]);
    }

    /**
     * @OA\Delete(
     *     path="/bill-operators/{id}",
     *     tags={"Bill Operators"},
     *     summary="Delete an operator",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Operator ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operator deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Operator deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $restaurantId = Auth::user()->restaurantId;
        $billOperator = BillOperator::where('restaurantId', $restaurantId)->findOrFail($id);
        $billOperator->delete();

        return response()->json(['message' => 'Operator deleted successfully']);
    }
}
