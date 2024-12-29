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
     *     description="Fetches the dashboard statistics for a specific restaurant, including today's, weekly, and monthly collection, total invoices, completed orders, and rejected orders.",
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
     *             @OA\Property(property="totalCompleteOrderToday", type="integer", example=10),
     *             @OA\Property(property="totalRejectOrderToday", type="integer", example=2),
     *             @OA\Property(property="weeklyCollection", type="number", example=12345.67),
     *             @OA\Property(property="totalInvoiceWeekly", type="integer", example=120),
     *             @OA\Property(property="totalCompleteOrderWeekly", type="integer", example=80),
     *             @OA\Property(property="totalRejectOrderWeekly", type="integer", example=15),
     *             @OA\Property(property="monthlyCollection", type="number", example=50000.00),
     *             @OA\Property(property="totalInvoiceMonthly", type="integer", example=450),
     *             @OA\Property(property="totalCompleteOrderMonthly", type="integer", example=300),
     *             @OA\Property(property="totalRejectOrderMonthly", type="integer", example=50)
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
        $date = Carbon::now(); // Get the current date and time
        $todayDate = $date->toDateString();
        $weekStartDate = $date->startOfWeek()->toDateString();
        $monthStartDate = $date->startOfMonth()->toDateString();

        // Fetch today's collection
        $todayCollection = Transaction::whereDate('created_at', $todayDate)
            ->where('restaurantId', $id)
            ->sum('total');

        // Fetch the total number of invoices for today
        $totalInvoiceToday = Transaction::whereDate('created_at', $todayDate)
            ->where('restaurantId', $id)
            ->count();

        // Fetch the total number of completed orders for today
        $totalCompleteOrderToday = Order::whereDate('created_at', $todayDate)
            ->where('restaurantId', $id)
            ->where('status', 'complete')
            ->count();

        // Fetch the total number of rejected orders for today
        $totalRejectOrderToday = Order::whereDate('created_at', $todayDate)
            ->where('restaurantId', $id)
            ->where('status', 'reject')
            ->count();

        // Fetch weekly statistics
        $weeklyCollection = Transaction::whereBetween('created_at', [$weekStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->sum('total');

        $totalInvoiceWeekly = Transaction::whereBetween('created_at', [$weekStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->count();

        $totalCompleteOrderWeekly = Order::whereBetween('created_at', [$weekStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->where('status', 'complete')
            ->count();

        $totalRejectOrderWeekly = Order::whereBetween('created_at', [$weekStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->where('status', 'reject')
            ->count();

        // Fetch monthly statistics
        $monthlyCollection = Transaction::whereBetween('created_at', [$monthStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->sum('total');

        $totalInvoiceMonthly = Transaction::whereBetween('created_at', [$monthStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->count();

        $totalCompleteOrderMonthly = Order::whereBetween('created_at', [$monthStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->where('status', 'complete')
            ->count();

        $totalRejectOrderMonthly = Order::whereBetween('created_at', [$monthStartDate, $todayDate])
            ->where('restaurantId', $id)
            ->where('status', 'reject')
            ->count();

        // Return the response in JSON format
        return response()->json([
            'todayCollection' => $todayCollection,
            'totalInvoiceToday' => $totalInvoiceToday,
            'totalCompleteOrderToday' => $totalCompleteOrderToday,
            'totalRejectOrderToday' => $totalRejectOrderToday,
            'weeklyCollection' => $weeklyCollection,
            'totalInvoiceWeekly' => $totalInvoiceWeekly,
            'totalCompleteOrderWeekly' => $totalCompleteOrderWeekly,
            'totalRejectOrderWeekly' => $totalRejectOrderWeekly,
            'monthlyCollection' => $monthlyCollection,
            'totalInvoiceMonthly' => $totalInvoiceMonthly,
            'totalCompleteOrderMonthly' => $totalCompleteOrderMonthly,
            'totalRejectOrderMonthly' => $totalRejectOrderMonthly,
        ]);
    }


    /**
     * @OA\Get(
     *     path="/dashboard/chart-data",
     *     summary="Get Dashboard Chart Data for a Year",
     *     description="Fetches the total collection, total invoices, completed orders, and rejected orders for a given year, grouped by month for a specific restaurant.",
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
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         required=true,
     *         description="The ID of the restaurant for which the chart data is being fetched.",
     *         @OA\Schema(
     *             type="string",
     *             example="12345"
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
     *         description="Invalid year or restaurantId parameter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid year or restaurantId format.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found for the given year and restaurant",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="No data found for the selected year and restaurant.")
     *         )
     *     )
     * )
     */
    public function getDashboardChartData(Request $request)
    {
        // Validate the incoming request to ensure 'year' and 'restaurantId' are provided
        $validated = $request->validate([
            'year' => 'required|integer', // Year must be provided
            'restaurantId' => 'required|string' // Restaurant ID must be provided
        ]);

        $year = $validated['year'];
        $restaurantId = $validated['restaurantId'];

        // Fetch total collection (sales) for the entire year
        $totalCollection = Transaction::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, sum(total) as total_collection')
            ->get();

        // Fetch total number of invoices for the entire year
        $totalInvoices = Transaction::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, count(id) as total_invoices')
            ->get();

        // Fetch total completed orders for the entire year
        $totalCompleteOrder = Order::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->where('status', 'complete')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, count(id) as complete_orders')
            ->get();

        // Fetch total rejected orders for the entire year
        $totalRejectOrder = Order::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->where('status', 'reject')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->selectRaw('MONTH(created_at) as month, count(id) as reject_orders')
            ->get();

        // Convert month numbers to short month names (e.g., Jan, Feb)
        $months = array_map(function ($month) {
            return date('M', mktime(0, 0, 0, $month, 1)); // Convert month number to short name
        }, range(1, 12));

        // Prepare the data for the chart
        $chartData = [
            'labels' => $months, // Labels for the X-axis (short month names)
            'datasets' => [
                [
                    'label' => 'Total Collection',
                    'data' => array_map(function ($month) use ($totalCollection) {
                        $data = $totalCollection->firstWhere('month', $month);
                        return $data ? $data->total_collection : 0;
                    }, range(1, 12)), // Use numeric months for matching
                    'borderColor' => 'rgba(54, 162, 235, 1)', // Line color for Total Collection
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Total Invoices',
                    'data' => array_map(function ($month) use ($totalInvoices) {
                        $data = $totalInvoices->firstWhere('month', $month);
                        return $data ? $data->total_invoices : 0;
                    }, range(1, 12)), // Use numeric months for matching
                    'borderColor' => 'rgba(75, 192, 192, 1)', // Line color for Total Invoices
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Completed Orders',
                    'data' => array_map(function ($month) use ($totalCompleteOrder) {
                        $data = $totalCompleteOrder->firstWhere('month', $month);
                        return $data ? $data->complete_orders : 0;
                    }, range(1, 12)), // Use numeric months for matching
                    'borderColor' => 'rgba(153, 102, 255, 1)', // Line color for Completed Orders
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Rejected Orders',
                    'data' => array_map(function ($month) use ($totalRejectOrder) {
                        $data = $totalRejectOrder->firstWhere('month', $month);
                        return $data ? $data->reject_orders : 0;
                    }, range(1, 12)), // Use numeric months for matching
                    'borderColor' => 'rgba(255, 99, 132, 1)', // Line color for Rejected Orders
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
            ],
        ];

        // Return the chart data as JSON
        return response()->json($chartData);
    }



    /**
     * @OA\Get(
     *     path="/dashboard/weekly-chart-data",
     *     summary="Get Weekly Chart Data for a Year",
     *     description="Fetches the total collection, total invoices, completed orders, and rejected orders for a given year, grouped by week for a specific restaurant.",
     *     operationId="getWeeklyChartData",
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
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="query",
     *         required=true,
     *         description="The ID of the restaurant for which the chart data is being fetched.",
     *         @OA\Schema(
     *             type="string",
     *             example="12345"
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
     *         description="Invalid year or restaurantId parameter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid year or restaurantId format.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found for the given year and restaurant",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="No data found for the selected year and restaurant.")
     *         )
     *     )
     * )
     */

    public function getWeeklyChartData(Request $request)
    {
        // Validate the incoming request to ensure 'year' and 'restaurantId' are provided
        $validated = $request->validate([
            'year' => 'required|integer', // Year must be provided
            'restaurantId' => 'required|string' // Restaurant ID must be provided
        ]);

        $year = $validated['year'];
        $restaurantId = $validated['restaurantId'];

        // Fetch total collection (sales) for the entire year, grouped by week
        $totalCollection = Transaction::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('WEEK(created_at)'))
            ->selectRaw('WEEK(created_at) as week, sum(total) as total_collection')
            ->get();

        // Fetch total number of invoices for the entire year, grouped by week
        $totalInvoices = Transaction::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('WEEK(created_at)'))
            ->selectRaw('WEEK(created_at) as week, count(id) as total_invoices')
            ->get();

        // Fetch total completed orders for the entire year, grouped by week
        $totalCompleteOrder = Order::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->where('status', 'complete')
            ->groupBy(DB::raw('WEEK(created_at)'))
            ->selectRaw('WEEK(created_at) as week, count(id) as complete_orders')
            ->get();

        // Fetch total rejected orders for the entire year, grouped by week
        $totalRejectOrder = Order::where('restaurantId', $restaurantId)
            ->whereYear('created_at', $year)
            ->where('status', 'reject')
            ->groupBy(DB::raw('WEEK(created_at)'))
            ->selectRaw('WEEK(created_at) as week, count(id) as reject_orders')
            ->get();

        // Determine the number of weeks in the given year
        $weeks = range(1, 52); // Assume a standard 52 weeks for simplicity

        // Prepare the data for the chart
        $chartData = [
            'labels' => array_map(function ($week) {
                return "Week $week"; // Format week labels as "Week 1", "Week 2", etc.
            }, $weeks),
            'datasets' => [
                [
                    'label' => 'Total Collection',
                    'data' => array_map(function ($week) use ($totalCollection) {
                        $data = $totalCollection->firstWhere('week', $week);
                        return $data ? $data->total_collection : 0;
                    }, $weeks),
                    'borderColor' => 'rgb(0, 153, 255)', // Line color for Total Collection
                    'backgroundColor' => 'rgba(0, 153, 255, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Total Invoices',
                    'data' => array_map(function ($week) use ($totalInvoices) {
                        $data = $totalInvoices->firstWhere('week', $week);
                        return $data ? $data->total_invoices : 0;
                    }, $weeks),
                    'borderColor' => 'rgb(0, 255, 255)', // Line color for Total Invoices
                    'backgroundColor' => 'rgba(0, 255, 255, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Completed Orders',
                    'data' => array_map(function ($week) use ($totalCompleteOrder) {
                        $data = $totalCompleteOrder->firstWhere('week', $week);
                        return $data ? $data->complete_orders : 0;
                    }, $weeks),
                    'borderColor' => 'rgb(85, 0, 255)', // Line color for Completed Orders
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
                [
                    'label' => 'Rejected Orders',
                    'data' => array_map(function ($week) use ($totalRejectOrder) {
                        $data = $totalRejectOrder->firstWhere('week', $week);
                        return $data ? $data->reject_orders : 0;
                    }, $weeks),
                    'borderColor' => 'rgb(255, 0, 55)', // Line color for Rejected Orders
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => false, // No fill for the line chart
                ],
            ],
        ];

        // Return the chart data as JSON
        return response()->json($chartData);
    }


    /**
     * @OA\Get(
     *     path="/reports/{id}/all-days",
     *     summary="Get All Days Report for a Specific Restaurant",
     *     tags={"Reports"},
     *     description="Fetches the total sum of transactions and count of transactions grouped by date for a specific restaurant.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful Response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="day", type="string", example="2024-12-01"),
     *                 @OA\Property(property="dailyTotal", type="number", format="float", example=350.00),
     *                 @OA\Property(property="totalTransactions", type="integer", example=10),
     *                 @OA\Property(property="id", type="integer", example=1)
     *             )
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
    public function allDaysReport($id)
    {
        // Fetch total sum of 'total' grouped by date (formatted) for the given restaurant
        $response = Transaction::where('restaurantId', $id)
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('SUM(total) as dailyTotal'),
                DB::raw('COUNT(id) as totalTransactions'),
            ) // Sum total for each day
            ->groupBy(DB::raw('DATE(created_at)'))  // Group by the date portion of created_at
            ->orderBy('day', 'asc')  // Order by day
            ->get();

        // Return the response in JSON format
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/getReportByType/{id}",
     *     summary="Get transaction reports grouped by payment type and day",
     *     description="Retrieve transaction reports grouped by payment type and day, ordered by the day in ascending order.",
     *     operationId="getReportByType",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the report type",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with transaction reports",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="paymentType",
     *                     type="string",
     *                     description="Payment type of the transaction"
     *                 ),
     *                 @OA\Property(
     *                     property="day",
     *                     type="string",
     *                     format="date",
     *                     description="Date of the transaction"
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="number",
     *                     format="float",
     *                     description="Total transaction amount for the payment type"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report type not found"
     *     )
     * )
     */

    public function getReportByType($id)
    {
        $response = Transaction::select(
            'payment_type',
            DB::raw('DATE(created_at) as day'),
            DB::raw('SUM(total) as total')
        )
            ->where('restaurantId', $id) // Assuming there's a reportTypeId to filter
            ->groupBy('payment_type', DB::raw('DATE(created_at)'))
            ->orderBy('day', 'asc')
            ->get();

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/getReportPaymentType",
     *     summary="Fetch report by payment type",
     *     description="Retrieve payment type statistics for a restaurant within a specific date range.",
     *     tags={"Reports"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="startDate",
     *                 type="string",
     *                 format="date",
     *                 description="Start date for the report (YYYY-MM-DD)",
     *                 example="2024-12-01"
     *             ),
     *             @OA\Property(
     *                 property="endDate",
     *                 type="string",
     *                 format="date",
     *                 description="End date for the report (YYYY-MM-DD)",
     *                 example="2024-12-10"
     *             ),
     *             @OA\Property(
     *                 property="restaurantId",
     *                 type="integer",
     *                 description="ID of the restaurant",
     *                 example=123
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="payment_type", type="string", example="Cash"),
     *                     @OA\Property(property="total_count", type="integer", example=45),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=15000)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties={
     *                     @OA\Property(type="array", @OA\Items(type="string"))
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function getReportPaymentType(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'restaurantId' => 'required|integer',
        ]);

        // Parse dates using Carbon
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $restaurantId = $request->restaurantId;

        // Query to fetch data
        $response = Transaction::where('restaurantId', $restaurantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_type', DB::raw('COUNT(*) as total_count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_type')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $response
        ]);
    }
}
