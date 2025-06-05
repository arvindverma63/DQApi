<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtherReportsController extends Controller
{
    /**
 * @OA\Get(
 *     path="/totalRevenueByDate",
 *     summary="Get total revenue grouped by date",
 *     description="Retrieve the total revenue for a specific restaurant within a date range, grouped by each date.",
 *     operationId="totalRevenueByDate",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Total revenue grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(property="totalRevenue", type="number", format="float", example=150.75)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function totalRevenueByDate(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = 0;
            }
            $groupedByDate[$date] += $transaction->total;
        }

        $response = [];
        foreach ($groupedByDate as $date => $totalRevenue) {
            $response[] = [
                'date' => $date,
                'totalRevenue' => round($totalRevenue, 2)
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/transactionsByPaymentType",
 *     summary="Get transactions grouped by payment type and date",
 *     description="Retrieve the number of transactions by payment type for a specific restaurant within a date range, grouped by each date.",
 *     operationId="transactionsByPaymentType",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transactions grouped by payment type and date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(
 *                     property="paymentTypes",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="paymentType", type="string", example="Credit Card"),
 *                         @OA\Property(property="transactionCount", type="integer", example=10)
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function transactionsByPaymentType(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            $paymentType = $transaction->payment_type;

            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = [];
            }

            if (!isset($groupedByDate[$date][$paymentType])) {
                $groupedByDate[$date][$paymentType] = 0;
            }

            $groupedByDate[$date][$paymentType]++;
        }

        $response = [];
        foreach ($groupedByDate as $date => $paymentTypes) {
            $sortedPaymentTypes = collect($paymentTypes)->map(function ($count, $type) {
                return [
                    'paymentType' => $type,
                    'transactionCount' => $count
                ];
            })->sortByDesc('transactionCount')->values();

            $response[] = [
                'date' => $date,
                'paymentTypes' => $sortedPaymentTypes
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/averageOrderValueByDate",
 *     summary="Get average order value grouped by date",
 *     description="Retrieve the average order value for a specific restaurant within a date range, grouped by each date.",
 *     operationId="averageOrderValueByDate",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Average order value grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(property="averageOrderValue", type="number", format="float", example=25.50)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function averageOrderValueByDate(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = ['total' => 0, 'count' => 0];
            }
            $groupedByDate[$date]['total'] += $transaction->total;
            $groupedByDate[$date]['count']++;
        }

        $response = [];
        foreach ($groupedByDate as $date => $data) {
            $response[] = [
                'date' => $date,
                'averageOrderValue' => round($data['total'] / $data['count'], 2)
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/discountUsageByDate",
 *     summary="Get discount usage grouped by date",
 *     description="Retrieve the total discount applied for a specific restaurant within a date range, grouped by each date.",
 *     operationId="discountUsageByDate",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Total discount applied grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(property="totalDiscount", type="number", format="float", example=50.25)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function discountUsageByDate(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = 0;
            }
            $groupedByDate[$date] += $transaction->discount;
        }

        $response = [];
        foreach ($groupedByDate as $date => $totalDiscount) {
            $response[] = [
                'date' => $date,
                'totalDiscount' => round($totalDiscount, 2)
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/tableUsageByDate",
 *     summary="Get table usage grouped by date",
 *     description="Retrieve the number of transactions per table for a specific restaurant within a date range, grouped by each date.",
 *     operationId="tableUsageByDate",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Table usage grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(
 *                     property="tables",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="tableNumber", type="string", example="Table 1"),
 *                         @OA\Property(property="transactionCount", type="integer", example=5)
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function tableUsageByDate(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            $tableNumber = $transaction->tableNumber ?? 'Unknown';

            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = [];
            }

            if (!isset($groupedByDate[$date][$tableNumber])) {
                $groupedByDate[$date][$tableNumber] = 0;
            }

            $groupedByDate[$date][$tableNumber]++;
        }

        $response = [];
        foreach ($groupedByDate as $date => $tables) {
            $sortedTables = collect($tables)->map(function ($count, $table) {
                return [
                    'tableNumber' => $table,
                    'transactionCount' => $count
                ];
            })->sortByDesc('transactionCount')->values();

            $response[] = [
                'date' => $date,
                'tables' => $sortedTables
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/taxCollectedByDate",
 *     summary="Get tax collected grouped by date",
 *     description="Retrieve the total tax collected for a specific restaurant within a date range, grouped by each date.",
 *     operationId="taxCollectedByDate",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Total tax collected grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(property="totalTax", type="number", format="float", example=12.75)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function taxCollectedByDate(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = 0;
            }
            $groupedByDate[$date] += $transaction->tax;
        }

        $response = [];
        foreach ($groupedByDate as $date => $totalTax) {
            $response[] = [
                'date' => $date,
                'totalTax' => round($totalTax, 2)
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/transactionCountByDate",
 *     summary="Get transaction count grouped by date",
 *     description="Retrieve the number of transactions for a specific restaurant within a date range, grouped by each date.",
 *     operationId="transactionCountByDate",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="startDate",
 *         in="query",
 *         required=true,
 *         description="Start date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-01")
 *     ),
 *     @OA\Parameter(
 *         name="endDate",
 *         in="query",
 *         required=true,
 *         description="End date of the range (YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-06-02")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction count grouped by date",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", example="2025-06-01"),
 *                 @OA\Property(property="transactionCount", type="integer", example=20)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No transactions found for the given restaurant ID",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No transactions found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="An error occurred while retrieving transactions."),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
public function transactionCountByDate(Request $request)
{
    try {
        $validated = $request->validate([
            'startDate' => 'string|required',
            'endDate' => 'string|required',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        $endDate = Carbon::parse($validated['endDate'])->endOfDay();
        $restaurantId = Auth::user()->restaurantId;

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('restaurantId', $restaurantId)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No transactions found.'
            ], 404);
        }

        $groupedByDate = [];
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at)->toDateString();
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = 0;
            }
            $groupedByDate[$date]++;
        }

        $response = [];
        foreach ($groupedByDate as $date => $count) {
            $response[] = [
                'date' => $date,
                'transactionCount' => $count
            ];
        }

        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving transactions.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
