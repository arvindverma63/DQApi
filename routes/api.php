<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminControllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminControllers\QrController;
use App\Http\Controllers\AdminControllers\CategoryController;
use App\Http\Controllers\AdminControllers\CustomerController;
use App\Http\Controllers\AdminControllers\DeliveryController;
use App\Http\Controllers\AdminControllers\DueController;
use App\Http\Controllers\AdminControllers\FeedbackController;
use App\Http\Controllers\AdminControllers\FirebaseNotificationController;
use App\Http\Controllers\AdminControllers\MenuController;
use App\Http\Controllers\AdminControllers\OrderController;
use App\Http\Controllers\AdminControllers\SupplierController;
use App\Http\Controllers\AdminControllers\InventoryController;
use App\Http\Controllers\AdminControllers\InvoiceController;
use App\Http\Controllers\AdminControllers\MenuInventoryController;
use App\Http\Controllers\AdminControllers\ReportController;
use App\Http\Controllers\AdminControllers\ReservationController;
use App\Http\Controllers\AdminControllers\SocialMediaController;
use App\Http\Controllers\AdminControllers\SubCategoryController;
use App\Http\Controllers\AdminControllers\TransactionController;
use App\Http\Controllers\AdminControllers\UserProfileController;
use App\Http\Controllers\UserControllers\MobileMenuController;
use App\Http\Controllers\UserControllers\UserMenuController;
use App\Http\Controllers\WebAppControllers\WebOrderController;

// Public routes
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
Route::get('/customer-report/{id}',[ReportController::class,'getCustomerReport']);
Route::get('/restaurant/{id}/logo',[UserProfileController::class,'getLogo']);

Route::get('/rest-profile/{id}', [UserProfileController::class, 'getProfile']);
Route::get('/getReportByType/{id}', [ReportController::class, 'getReportByType']);
Route::get('/report-by-table',[ReportController::class,'getReportByTableNumber']);


Route::post('/admin/feedback/add', [FeedbackController::class, 'addFeedback']);
Route::get('/feedbacks/{id}', [FeedbackController::class, 'getAllFeedbacks']);
Route::post('/profile/{id}/image',[UserProfileController::class,'uploadImage']);
// Protected routes
Route::middleware(['auth:api'])->group(function () {


    // Logout and user info
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Super Admin routes (requires 'super' role)
    Route::middleware(['role:super'])->group(function () {
        Route::prefix('super-admin')->group(function () {
            // Create a new restaurant
            Route::post('/add-restaurant', [UserController::class, 'addRestaurant'])->name('add.restaurant');

            // User management routes
            Route::get('/users', [UserController::class, 'getAllUsers'])->name('get.all.users');
            Route::get('/users/{id}', [UserController::class, 'getUser'])->name('get.user');
            Route::put('/users/{id}', [UserController::class, 'updateUser'])->name('update.user');
            Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->name('delete.user');
            Route::delete('/users/{id}/force', [UserController::class, 'forceDeleteUser'])->name('force.delete.user');


        });

        Route::get('/social-media',[SocialMediaController::class,'index']);
            Route::post('/social-media',[SocialMediaController::class,'store']);
            Route::get('/social-media/{id}',[SocialMediaController::class,'show']);
            Route::put('/social-media/{id}',[SocialMediaController::class,'update']);
            Route::delete('/social-media/{id}',[SocialMediaController::class,'destroy']);
    });

    // Admin routes (requires 'admin' role)
    Route::middleware(['role:admin'])->group(function () {

        // User-specific routes
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::get('user/dashboard', [UserController::class, 'dashboard']);

        Route::put('/profile/{id}', [UserProfileController::class, 'updateProfile']);

        Route::prefix('admin')->group(function () {
            // Create a new subcategory
            Route::post('/subcategories', [SubCategoryController::class, 'addCategory']);

            // Get all subcategories
            Route::get('/subcategories', [SubCategoryController::class, 'getAllCategories']);

            // Get a specific subcategory
            Route::get('/subcategories/{id}', [SubCategoryController::class, 'getCategory']);

            // Update a subcategory
            Route::put('/subcategories/{id}', [SubCategoryController::class, 'updateCategory']);

            // Delete a subcategory
            Route::delete('/subcategories/{id}', [SubCategoryController::class, 'deleteCategory']);
        });

        Route::get('/social-media',[SocialMediaController::class,'index']);
            Route::post('/social-media',[SocialMediaController::class,'store']);
            Route::get('/social-media/{id}',[SocialMediaController::class,'show']);
            Route::put('/social-media/{id}',[SocialMediaController::class,'update']);
            Route::delete('/social-media/{id}',[SocialMediaController::class,'destroy']);




        // QR management

        Route::put('/qr/update/{id}', [QrController::class, 'updateQr']);
        Route::delete('/qr/delete/{id}', [QrController::class, 'deleteQr']);

        // Category management
        Route::post('/category', [CategoryController::class, 'addCategory']);
        Route::get('/categories', [CategoryController::class, 'getAllCategories']);
        Route::get('/category/{id}', [CategoryController::class, 'getCategoryById']);
        Route::post('/category/{id}', [CategoryController::class, 'updateCategory']);
        Route::delete('/category/{id}', [CategoryController::class, 'deleteCategory']);

        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
        Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
        Route::get('/menu/{id}', [MenuController::class, 'show'])->name('menu.show');
        Route::post('/menu/update/{id}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');
        Route::put('/menus/status', [MenuController::class, 'updateStatus']);

        // Menu management

        // QR management
        Route::post('/qr/create', [QrController::class, 'createQr']);
        Route::get('/qr/{id}', [QrController::class, 'getQr']);
        Route::put('/qr/update/{id}', [QrController::class, 'updateQr']);
        Route::delete('/qr/delete/{id}', [QrController::class, 'deleteQr']);

        // Order management
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('orders.index');
            Route::post('/', [OrderController::class, 'store'])->name('orders.store');
            Route::get('/{id}', [OrderController::class, 'show'])->name('orders.show');
            Route::put('/{id}', [OrderController::class, 'update'])->name('orders.update');
            Route::delete('/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
            Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
            Route::get('/notification/{id}', [OrderController::class, 'getNotification']);
            Route::put('/status/notification/{id}', [OrderController::class, 'updateNotificationStatus']);
        });
        Route::prefix('suppliers')->group(function () {
            // Get all suppliers
            Route::get('/', [SupplierController::class, 'getSuppliers']);

            // Get a specific supplier by ID
            Route::get('/{id}', [SupplierController::class, 'getSupplier']);

            // Create a new supplier
            Route::post('/', [SupplierController::class, 'createSupplier']);

            // Update a specific supplier
            Route::put('/{id}', [SupplierController::class, 'updateSupplier']);

            // Delete a specific supplier
            Route::delete('/{id}', [SupplierController::class, 'deleteSupplier']);
        });

        Route::prefix('dues')->group(function () {
            Route::get('/byRestaurantId/{restaurantId}', [DueController::class, 'index']); // Get all dues
            Route::post('/', [DueController::class, 'store']); // Create a due record
            Route::get('/{id}', [DueController::class, 'show']); // Get a specific due
            Route::put('/{id}', [DueController::class, 'update']); // Update a due
            Route::delete('/{id}', [DueController::class, 'destroy']); // Delete a due
        });



        Route::post('/send-notification', [FirebaseNotificationController::class, 'sendNotification']);

        Route::prefix('inventories')->group(function () {
            // Get all inventory items
            Route::get('/', [InventoryController::class, 'getAllInventory']);

            // Get a specific inventory item by ID
            Route::get('/{id}', [InventoryController::class, 'getInventory']);

            // Create a new inventory item
            Route::post('/', [InventoryController::class, 'createInventory']);

            // Update a specific inventory item
            Route::put('/{id}', [InventoryController::class, 'updateInventory']);

            // Delete a specific inventory item
            Route::delete('/{id}', [InventoryController::class, 'deleteInventory']);
        });
    });
    Route::get('/reservations/AllByRestaurantId/{id}', [ReservationController::class, 'index']); // List all reservations
    Route::prefix('reservations')->group(function () {
        Route::post('/', [ReservationController::class, 'store']); // Create a new reservation
        Route::get('/{id}', [ReservationController::class, 'show']); // Get a single reservation by ID
        Route::put('/{id}', [ReservationController::class, 'update']); // Update a reservation by ID
        Route::delete('/{id}', [ReservationController::class, 'destroy']); // Delete a reservation by ID
    });

    Route::prefix('menu_inventory')->group(function () {
        // Route to get all menu inventory items
        Route::get('/', [MenuInventoryController::class, 'getAllMenuInventory'])
            ->name('menu-inventory.getAll');

        // Route to get a specific menu inventory item by ID
        Route::get('/{id}', [MenuInventoryController::class, 'getMenuInventory'])
            ->name('menu-inventory.get');

        // Route to create a new menu inventory item
        Route::post('/', [MenuInventoryController::class, 'createMenuInventory'])
            ->name('menu-inventory.create');

        // Route to create or update menu inventory item
        Route::post('/save', [MenuInventoryController::class, 'saveInventoryItem'])
            ->name('menu-inventory.save');

        // Route to create or update menu inventory items
        Route::post('/save-all', [MenuInventoryController::class, 'saveInventoryItems'])
            ->name('menu-inventory.save.all');

        // Route to update an existing menu inventory item
        Route::put('/{id}', [MenuInventoryController::class, 'updateMenuInventory'])
            ->name('menu-inventory.update');

        // Route to delete a menu inventory item by ID
        Route::delete('/{id}', [MenuInventoryController::class, 'deleteMenuInventory'])
            ->name('menu-inventory.delete');
    });

    Route::post('/customer', [CustomerController::class, 'createCustomer'])->name('create.customer');
    Route::delete('/customer/{id}', [CustomerController::class, 'deleteCustomer'])->name('delete.customer');


    Route::post('/transactions', [TransactionController::class, 'addTransaction']);
    Route::get('/transactions/{id}', [TransactionController::class, 'getTransaction']);
    Route::get('/transactionById/{id}', [TransactionController::class, 'getTransactionById']);

    Route::get('/reports', [ReportController::class, 'getDashboardStats']);



    Route::apiResource('deliveries', DeliveryController::class);
    // Regular user routes (requires 'user' role)
    Route::middleware(['role:user'])->group(function () {
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::get('user/dashboard', [UserController::class, 'dashboard']);

        Route::get('/users/menu',[UserMenuController::class,'getMenu']);
    });
});

//Web App Api's

Route::get('/webMenu', [WebOrderController::class, 'menu']);
Route::post('/addOrder', [WebOrderController::class, 'addTransaction']);
Route::get('/webMenu/categories', [WebOrderController::class, 'getAllCategories']);
Route::get('/menu/category/{id}', [WebOrderController::class, 'searchMenuByCategory']);
// Api's for mobile app

Route::get('/app/menu', [MobileMenuController::class, 'getMenu']);
Route::get('/app/menu/all', [MobileMenuController::class, 'getAllMenu']);
Route::post('/send-invoice-email', [InvoiceController::class, 'sendInvoiceEmail']);

Route::get('/deliveries/restaurant/{restaurantId}', [DeliveryController::class, 'getDeliveryByRestaurantId']);
