<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RestaurantExpense;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Restaurant Expenses",
 *     description="Manage expenses per authenticated restaurant"
 * )
 */
class RestaurantExpenseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/restaurant-expenses",
     *     summary="Get all expenses for the authenticated user's restaurant",
     *     tags={"Restaurant Expenses"},
     *
     *     @OA\Response(response=200, description="List of expenses")
     * )
     */
    public function index()
    {
        $restaurantId = Auth::user()->restaurantId;

        $expenses = RestaurantExpense::where('restaurantId', $restaurantId)->get();

        return response()->json($expenses, 200);
    }

    /**
     * @OA\Post(
     *     path="/restaurant-expenses",
     *     summary="Create a new expense for the authenticated user's restaurant",
     *     tags={"Restaurant Expenses"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RestaurantExpense")
     *     ),
     *     @OA\Response(response=201, description="Expense created")
     * )
     */
    public function store(Request $request)
    {
        $restaurantId = Auth::user()->restaurantId;

        $data = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'amount' => 'required|numeric',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $data['restaurantId'] = $restaurantId;

        $expense = RestaurantExpense::create($data);

        return response()->json($expense, 201);
    }

    /**
     * @OA\Get(
     *     path="/restaurant-expenses/{id}",
     *     summary="Get a specific expense for the authenticated user's restaurant",
     *     tags={"Restaurant Expenses"},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Expense found"),
     *     @OA\Response(response=404, description="Expense not found")
     * )
     */
    public function show($id)
    {
        $restaurantId = Auth::user()->restaurantId;

        $expense = RestaurantExpense::where('restaurantId', $restaurantId)->find($id);

        if (!$expense) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($expense, 200);
    }

    /**
     * @OA\Put(
     *     path="/restaurant-expenses/{id}",
     *     summary="Update an expense for the authenticated user's restaurant",
     *     tags={"Restaurant Expenses"},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RestaurantExpense")),
     *     @OA\Response(response=200, description="Expense updated"),
     *     @OA\Response(response=404, description="Expense not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $restaurantId = Auth::user()->restaurantId;

        $expense = RestaurantExpense::where('restaurantId', $restaurantId)->find($id);

        if (!$expense) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $expense->update($request->only([
            'expense_date',
            'category',
            'description',
            'amount',
            'payment_method'
        ]));

        return response()->json($expense, 200);
    }

    /**
     * @OA\Delete(
     *     path="/restaurant-expenses/{id}",
     *     summary="Delete an expense for the authenticated user's restaurant",
     *     tags={"Restaurant Expenses"},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted successfully"),
     *     @OA\Response(response=404, description="Expense not found")
     * )
     */
    public function destroy($id)
    {
        $restaurantId = Auth::user()->restaurantId;

        $expense = RestaurantExpense::where('restaurantId', $restaurantId)->find($id);

        if (!$expense) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $expense->delete();

        return response()->json(null, 204);
    }
}
