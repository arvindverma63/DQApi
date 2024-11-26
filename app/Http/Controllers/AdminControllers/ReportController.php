<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/reports/{id}",
     *     summary="Get Dashboard Statistics for a Specific Restaurant",
     *     tags={"Reports"},
     *     description="Fetches the dashboard statistics for a specific restaurant, including today's collection, total invoices, completed orders, and rejected orders.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="todayCollection", type="number", example=1234.56),
     *             @OA\Property(property="totalInvoiceToday", type="integer", example=15),
     *             @OA\Property(property="totalCompleteOrder", type="integer", example=10),
     *             @OA\Property(property="totalRejectOrder", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant Not Found"
     *     )
     * )
     */
    public function getDashboardStats($id)
    {
        $date = Carbon::now()->toDateString(); // Ensure only the date part is used

        // Fetch today's collection
        $todayCollection = Transaction::whereDate('created_at', $date)
            ->where('restaurantId', $id)
            ->sum('total');

        // Fetch the total number of invoices for today
        $totalInvoiceToday = Transaction::whereDate('created_at', $date)
            ->where('restaurantId', $id)
            ->count();

        // Fetch the total number of completed orders for today
        $totalCompleteOrder = Order::whereDate('created_at', $date)
            ->where('restaurantId', $id)
            ->where('status', 'complete')
            ->count();

        // Fetch the total number of rejected orders for today
        $totalRejectOrder = Order::whereDate('created_at', $date)
            ->where('restaurantId', $id)
            ->where('status', 'reject')
            ->count();

        // Return the response in JSON format
        return response()->json([
            'todayCollection' => $todayCollection,
            'totalInvoiceToday' => $totalInvoiceToday,
            'totalCompleteOrder' => $totalCompleteOrder,
            'totalRejectOrder' => $totalRejectOrder,
        ]);
    }
}
