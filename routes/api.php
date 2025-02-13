<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    SuperAdminControllers\UserController,
    AdminControllers\QrController,
    AdminControllers\CategoryController,
    AdminControllers\CustomerController,
    AdminControllers\DueController,
    AdminControllers\FeedbackController,
    AdminControllers\FirebaseNotificationController,
    AdminControllers\MenuController,
    AdminControllers\OrderController,
    AdminControllers\SupplierController,
    AdminControllers\InventoryController,
    AdminControllers\InvoiceController,
    AdminControllers\MenuInventoryController,
    AdminControllers\ReportController,
    AdminControllers\ReservationController,
    AdminControllers\SocialMediaController,
    AdminControllers\TransactionController,
    AdminControllers\UserProfileController,
    UserControllers\MobileMenuController,
    WebAppControllers\WebOrderController
};

// 🔹 AUTH & PUBLIC ROUTES
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);

Route::get('/reports/{id}', [ReportController::class, 'getDashboardStats']);
Route::get('/dashboard/chart-data', [ReportController::class, 'getDashboardChartData']);
Route::get('/dashboard/weekly-chart-data', [ReportController::class, 'getWeeklyChartData']);

Route::post('/qr/create', [QrController::class, 'createQr']);
Route::get('/qr/{id}', [QrController::class, 'getQr']);
Route::get('/reports/{id}/all-days', [ReportController::class, 'allDaysReport']);
Route::post('/getReportPaymentType', [ReportController::class, 'getReportPaymentType']);

Route::get('/getReportByType/{id}', [ReportController::class, 'getReportByType']);

Route::post('/admin/feedback/add', [FeedbackController::class, 'addFeedback']);
Route::get('/feedbacks/{id}', [FeedbackController::class, 'getAllFeedbacks']);

// 🔹 PROTECTED ROUTES (Auth Required)
Route::middleware(['auth:api'])->group(function () {

    // Logout & User Info
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // 🔹 SUPER ADMIN ROUTES
    Route::middleware(['role:super'])->prefix('super-admin')->group(function () {
        Route::post('/add-restaurant', [UserController::class, 'addRestaurant'])->name('add.restaurant');

        // User Management
        Route::get('/users', [UserController::class, 'getAllUsers'])->name('get.all.users');
        Route::get('/users/{id}', [UserController::class, 'getUser'])->name('get.user');
        Route::put('/users/{id}', [UserController::class, 'updateUser'])->name('update.user');
        Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->name('delete.user');
        Route::delete('/users/{id}/force', [UserController::class, 'forceDeleteUser'])->name('force.delete.user');
    });

    // 🔹 ADMIN ROUTES
    Route::middleware(['role:admin|super'])->group(function () {

        // User Profile
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::get('user/dashboard', [UserController::class, 'dashboard']);
        Route::get('/rest-profile/{id}', [UserProfileController::class, 'getProfile']);
        Route::post('/profile/{id}', [UserProfileController::class, 'updateProfile']);

        // QR Management
        Route::put('/qr/update/{id}', [QrController::class, 'updateQr']);
        Route::delete('/qr/delete/{id}', [QrController::class, 'deleteQr']);

        // Category Management
        Route::resource('categories', CategoryController::class);

        // Menu Management
        Route::resource('menu', MenuController::class);
        Route::put('/menus/status', [MenuController::class, 'updateStatus']);

        // Order Management
        Route::resource('orders', OrderController::class);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('/orders/notification/{id}', [OrderController::class, 'getNotification']);
        Route::put('/status/notification/{id}', [OrderController::class, 'updateNotificationStatus']);

        // Supplier Management
        Route::resource('suppliers', SupplierController::class);

        // Due Management
        Route::resource('dues', DueController::class);

        // Social Media Management
        Route::apiResource('social-media', SocialMediaController::class);

        // Notifications
        Route::post('/send-notification', [FirebaseNotificationController::class, 'sendNotification']);

        // Inventory Management
        Route::resource('inventories', InventoryController::class);

        // Reservation Management
        Route::resource('reservations', ReservationController::class);

        // Menu Inventory Management
        Route::resource('menu-inventory', MenuInventoryController::class);

        // Customer Management
        Route::resource('customers', CustomerController::class);

        // Transactions
        Route::resource('transactions', TransactionController::class);

        // Reports
        Route::get('/reports', [ReportController::class, 'getDashboardStats']);
    });

    // 🔹 REGULAR USER ROUTES
    Route::middleware(['role:user'])->group(function () {
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::get('user/dashboard', [UserController::class, 'dashboard']);
    });
});

// 🔹 WEB APP ROUTES
Route::get('/webMenu', [WebOrderController::class, 'menu']);
Route::post('/addOrder', [WebOrderController::class, 'addTransaction']);
Route::get('/webMenu/categories', [WebOrderController::class, 'getAllCategories']);
Route::get('/menu/category/{id}', [WebOrderController::class, 'searchMenuByCategory']);

// 🔹 MOBILE APP ROUTES
Route::get('/app/menu', [MobileMenuController::class, 'getMenu']);
Route::get('/app/menu/all', [MobileMenuController::class, 'getAllMenu']);
Route::post('/send-invoice-email', [InvoiceController::class, 'sendInvoiceEmail']);
