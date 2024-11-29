<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;
use DB;
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

    /**
 * @OA\Get(
 *     path="/dashboard/chart-data",
 *     summary="Get Dashboard Chart Data for a Year",
 *     description="Fetches the total collection, total invoices, completed orders, and rejected orders for a given year, grouped by month.",
 *     operationId="getDashboardChartData",
 *     tags={"Reports"},
 *     @OA\Parameter(
 *         name="year",
 *         in="query",
 *         required=true,
 *         description="The year for which the chart data is requested.",
 *         @OA\Schema(
 *             type="integer",
 *             example=2024
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="labels",
 *                 type="array",
 *                 @OA\Items(type="integer", example=1)
 *             ),
 *             @OA\Property(
 *                 property="datasets",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="label", type="string", example="Total Collection"),
 *                     @OA\Property(property="data", type="array", @OA\Items(type="integer", example=1000)),
 *                     @OA\Property(property="borderColor", type="string", example="rgba(54, 162, 235, 1)"),
 *                     @OA\Property(property="backgroundColor", type="string", example="rgba(54, 162, 235, 0.2)"),
 *                     @OA\Property(property="fill", type="boolean", example=false)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid year parameter",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Invalid year format.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Data not found for the given year",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="No data found for the selected year.")
 *         )
 *     )
 * )
 */
    public function getDashboardChartData(Request $request)
    {
        // Validate the incoming request to ensure 'year' is provided
        $validated = $request->validate([
            'year' => 'required|integer', // Year must be provided
        ]);

        $year = $validated['year'];

        // Fetch total collection (sales) for the entire year
        $totalCollection = Transaction::whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, sum(total) as total_collection')
            ->get();

        // Fetch total number of invoices for the entire year
        $totalInvoices = Transaction::whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, count(id) as total_invoices')
            ->get();

        // Fetch total completed orders for the entire year
        $totalCompleteOrder = Order::whereYear('created_at', $year)
            ->where('status', 'complete')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, count(id) as complete_orders')
            ->get();

        // Fetch total rejected orders for the entire year
        $totalRejectOrder = Order::whereYear('created_at', $year)
            ->where('status', 'reject')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, count(id) as reject_orders')
            ->get();

        // Prepare the data for the chart
        $months = range(1, 12);  // Months from 1 to 12
        $chartData = [
            'labels' => $months, // Labels for the X-axis (months of the year)
            'datasets' => [
                [
                    'label' => 'Total Collection',
                    'data' => array_map(function ($month) use ($totalCollection) {
                        $data = $totalCollection->firstWhere('month', $month);
                        return $data ? $data->total_collection : 0;
                    }, $months),
                    'borderColor' => 'rgba(54, 162, 235, 1)', // Line color for Total Collection
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Total Invoices',
                    'data' => array_map(function ($month) use ($totalInvoices) {
                        $data = $totalInvoices->firstWhere('month', $month);
                        return $data ? $data->total_invoices : 0;
                    }, $months),
                    'borderColor' => 'rgba(75, 192, 192, 1)', // Line color for Total Invoices
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Completed Orders',
                    'data' => array_map(function ($month) use ($totalCompleteOrder) {
                        $data = $totalCompleteOrder->firstWhere('month', $month);
                        return $data ? $data->complete_orders : 0;
                    }, $months),
                    'borderColor' => 'rgba(153, 102, 255, 1)', // Line color for Completed Orders
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Rejected Orders',
                    'data' => array_map(function ($month) use ($totalRejectOrder) {
                        $data = $totalRejectOrder->firstWhere('month', $month);
                        return $data ? $data->reject_orders : 0;
                    }, $months),
                    'borderColor' => 'rgba(255, 99, 132, 1)', // Line color for Rejected Orders
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
            ],
        ];

        // Return the chart data as JSON
        return response()->json($chartData);
    }

}
