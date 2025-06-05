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
     *     summary="Get total revenue with full transaction details grouped by date",
     *     description="Retrieve the total revenue for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         description="Total revenue with transaction details grouped by date",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="date", type="string", example="2025-06-01"),
     *                 @OA\Property(property="totalRevenue", type="number", format="float", example=150.75),
     *                 @OA\Property(
     *                     property="transactions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=101),
     *                         @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                         @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                         @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                         @OA\Property(property="total", type="number", format="float", example=53.50),
     *                         @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                         @OA\Property(property="restaurantId", type="integer", example=1),
     *                         @OA\Property(property="addedBy", type="integer", example=201),
     *                         @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                         @OA\Property(property="note", type="string", example="Special request"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
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
                    $groupedByDate[$date] = ['totalRevenue' => 0, 'transactions' => []];
                }
                $groupedByDate[$date]['totalRevenue'] += $transaction->total;
                $groupedByDate[$date]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $data) {
                $response[] = [
                    'date' => $date,
                    'totalRevenue' => round($data['totalRevenue'], 2),
                    'transactions' => $data['transactions']
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
     *     summary="Get transactions by payment type with full transaction details grouped by date",
     *     description="Retrieve the number of transactions by payment type for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         description="Transactions by payment type with transaction details grouped by date",
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
     *                         @OA\Property(property="transactionCount", type="integer", example=10),
     *                         @OA\Property(
     *                             property="transactions",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="user_id", type="integer", example=101),
     *                                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                                 @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                                 @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                                 @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                                 @OA\Property(property="total", type="number", format="float", example=53.50),
     *                                 @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                                 @OA\Property(property="restaurantId", type="integer", example=1),
     *                                 @OA\Property(property="addedBy", type="integer", example=201),
     *                                 @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                                 @OA\Property(property="note", type="string", example="Special request"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                             )
     *                         )
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
                    $groupedByDate[$date][$paymentType] = [
                        'paymentType' => $paymentType,
                        'transactionCount' => 0,
                        'transactions' => []
                    ];
                }

                $groupedByDate[$date][$paymentType]['transactionCount']++;
                $groupedByDate[$date][$paymentType]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $paymentTypes) {
                $sortedPaymentTypes = collect($paymentTypes)->sortByDesc('transactionCount')->values();
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
     *     summary="Get average order value with full transaction details grouped by date",
     *     description="Retrieve the average order value for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         description="Average order value with transaction details grouped by date",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="date", type="string", example="2025-06-01"),
     *                 @OA\Property(property="averageOrderValue", type="number", format="float", example=25.50),
     *                 @OA\Property(
     *                     property="transactions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=101),
     *                         @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                         @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                         @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                         @OA\Property(property="total", type="number", format="float", example=53.50),
     *                         @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                         @OA\Property(property="restaurantId", type="integer", example=1),
     *                         @OA\Property(property="addedBy", type="integer", example=201),
     *                         @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                         @OA\Property(property="note", type="string", example="Special request"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
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
                    $groupedByDate[$date] = ['total' => 0, 'count' => 0, 'transactions' => []];
                }
                $groupedByDate[$date]['total'] += $transaction->total;
                $groupedByDate[$date]['count']++;
                $groupedByDate[$date]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $data) {
                $response[] = [
                    'date' => $date,
                    'averageOrderValue' => round($data['total'] / $data['count'], 2),
                    'transactions' => $data['transactions']
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
     *     summary="Get discount usage with full transaction details grouped by date",
     *     description="Retrieve the total discount applied for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         description="Total discount applied with transaction details grouped by date",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="date", type="string", example="2025-06-01"),
     *                 @OA\Property(property="totalDiscount", type="number", format="float", example=50.25),
     *                 @OA\Property(
     *                     property="transactions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=101),
     *                         @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                         @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                         @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                         @OA\Property(property="total", type="number", format="float", example=53.50),
     *                         @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                         @OA\Property(property="restaurantId", type="integer", example=1),
     *                         @OA\Property(property="addedBy", type="integer", example=201),
     *                         @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                         @OA\Property(property="note", type="string", example="Special request"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
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
                    $groupedByDate[$date] = ['totalDiscount' => 0, 'transactions' => []];
                }
                $groupedByDate[$date]['totalDiscount'] += $transaction->discount;
                $groupedByDate[$date]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $data) {
                $response[] = [
                    'date' => $date,
                    'totalDiscount' => round($data['totalDiscount'], 2),
                    'transactions' => $data['transactions']
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
     *     summary="Get table usage with full transaction details grouped by date",
     *     description="Retrieve the number of transactions per table for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         description="Table usage with transaction details grouped by date",
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
     *                         @OA\Property(property="transactionCount", type="integer", example=5),
     *                         @OA\Property(
     *                             property="transactions",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="user_id", type="integer", example=101),
     *                                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                                 @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                                 @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                                 @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                                 @OA\Property(property="total", type="number", format="float", example=53.50),
     *                                 @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                                 @OA\Property(property="restaurantId", type="integer", example=1),
     *                                 @OA\Property(property="addedBy", type="integer", example=201),
     *                                 @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                                 @OA\Property(property="note", type="string", example="Special request"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                             )
     *                         )
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
                    $groupedByDate[$date][$tableNumber] = [
                        'tableNumber' => $tableNumber,
                        'transactionCount' => 0,
                        'transactions' => []
                    ];
                }

                $groupedByDate[$date][$tableNumber]['transactionCount']++;
                $groupedByDate[$date][$tableNumber]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $tables) {
                $sortedTables = collect($tables)->sortByDesc('transactionCount')->values();
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
     *     summary="Get tax collected with full transaction details grouped by date",
     *     description="Retrieve the total tax collected for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         description="Total tax collected with transaction details grouped by date",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="date", type="string", example="2025-06-01"),
     *                 @OA\Property(property="totalTax", type="number", format="float", example=12.75),
     *                 @OA\Property(
     *                     property="transactions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=101),
     *                         @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                         @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                         @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                         @OA\Property(property="total", type="number", format="float", example=53.50),
     *                         @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                         @OA\Property(property="restaurantId", type="integer", example=1),
     *                         @OA\Property(property="addedBy", type="integer", example=201),
     *                         @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                         @OA\Property(property="note", type="string", example="Special request"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
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
                    $groupedByDate[$date] = ['totalTax' => 0, 'transactions' => []];
                }
                $groupedByDate[$date]['totalTax'] += $transaction->tax;
                $groupedByDate[$date]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $data) {
                $response[] = [
                    'date' => $date,
                    'totalTax' => round($data['totalTax'], 2),
                    'transactions' => $data['transactions']
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
     *     summary="Get transaction count with full transaction details grouped by date",
     *     description="Retrieve the number of transactions for a specific restaurant within a date range, grouped by each date, including all transaction columns.",
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
     *         @OA\Schema(type="string", format="date", example="2025-5-02")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction count with transaction details grouped by date",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="date", type="string", example="2025-06-01"),
     *                 @OA\Property(property="transactionCount", type="integer", example=20),
     *                 @OA\Property(
     *                     property="transactions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=101),
     *                         @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="tax", type="number", format="float", example=5.50),
     *                         @OA\Property(property="discount", type="number", format="float", example=2.00),
     *                         @OA\Property(property="sub_total", type="number", format="float", example=50.00),
     *                         @OA\Property(property="total", type="number", format="float", example=53.50),
     *                         @OA\Property(property="payment_type", type="string", example="Credit Card"),
     *                         @OA\Property(property="restaurantId", type="integer", example=1),
     *                         @OA\Property(property="addedBy", type="integer", example=201),
     *                         @OA\Property(property="tableNumber", type="string", example="Table 1"),
     *                         @OA\Property(property="note", type="string", example="Special request"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T12:00:00Z"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
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
                    $groupedByDate[$date] = ['transactionCount' => 0, 'transactions' => []];
                }
                $groupedByDate[$date]['transactionCount']++;
                $groupedByDate[$date]['transactions'][] = $transaction->toArray();
            }

            $response = [];
            foreach ($groupedByDate as $date => $data) {
                $response[] = [
                    'date' => $date,
                    'transactionCount' => $data['transactionCount'],
                    'transactions' => $data['transactions']
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
