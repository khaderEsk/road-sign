<?php

use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RoadSignController;
use App\Http\Controllers\Api\V1\AuthenticationController;
use App\Http\Controllers\Api\V1\BookingCustomerController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\SYRController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer'], function () {
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/verify', [AuthenticationController::class, 'verify']);
    Route::post('/resend-otp', [AuthenticationController::class, 'resendOtp']);

    Route::post('/password/code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('/password/verify', [PasswordResetController::class, 'verifyCode']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);



    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::get('/details', [SYRController::class, 'index']);
    Route::get('/roadSing', [RoadSignController::class, 'getAllRoadSing']);
    Route::get('/RoadSingSites', [RoadSignController::class, 'RoadSingSites']);
    Route::post('/getRoadSingsFilter', [RoadSignController::class, 'getRoadSingsFilter']);
    Route::get('/roadSing/{id}', [RoadSignController::class, 'getById']);
    Route::post('/recommendByLocationAndBudget', [AIController::class, 'recommendByLocationAndBudget']);

    Route::group(['middleware' => ['auth:customer', 'customer.role']], function () {
        Route::apiResource('favorite', FavoriteController::class);
        Route::get('/myProfile', [AuthenticationController::class, 'profile']);
        Route::post('/update-myProfile', [AuthenticationController::class, 'updateProfile']);
        Route::apiResource('booking', BookingCustomerController::class);
        Route::post('get-calculate-Amount', [BookingCustomerController::class, 'calculateAmounts']);
        Route::apiResource('payments', PaymentController::class);
        Route::get('/payment/accepted', [PaymentController::class, 'getPaymentsAccepted']);
        Route::get('/payment/unaccepted', [PaymentController::class, 'getPaymentsUnaccepted']);
    });
    Route::get('/index', [RoadSignController::class, 'index']);
});



// Route::post('login', [AuthController::class, 'login']);
// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('templates', TemplateController::class);
//     Route::apiResource('customers', CustomerController::class);
//     Route::apiResource('contracts', ContractController::class);
//     Route::apiResource('brokers', BrokerController::class);
//     Route::apiResource('products', ProductController::class);
//     Route::apiResource('road-signs', RoadSignController::class);
//     Route::apiResource('orders', OrderController::class);
//     Route::apiResource('payments', PaymentController::class);
//     Route::apiResource('bookings', BookingController::class);
//     Route::apiResource('cities', CityController::class);
//     Route::apiResource('regions', RegionController::class);
//     Route::apiResource('users', UserController::class);
//     Route::apiResource('discounts', DiscountController::class);
//     Route::get('get-templates-type', [TemplateController::class, 'getTemplatesType']);
//     Route::get('get-templates-model', [TemplateController::class, 'getTemplatesModel']);
//     Route::get('get-templates-count-by-model', [TemplateController::class, 'getTemplatesModel']);
//     Route::get('summaries', [DashboardController::class, 'summaries']);
//     Route::get('/profile', [AuthController::class, 'profile']);
//     Route::post('/update-profile', [AuthController::class, 'updateProfile']);
//     Route::get('/get-active-cities', [CityController::class, 'getActive']);
//     Route::get('/get-active-regions-by-city', [RegionController::class, 'getActiveByCity']);
//     Route::get('roles', [RoleController::class, 'index']);
//     Route::get('get-activities-user-by-id', [ActivityController::class, 'getActivityiesUserById']);
//     Route::get('get-template-products', [ProductController::class, 'getTemplateProducts']);
//     Route::Post('update-status-order/{id}', [OrderController::class, 'updateStatus']);
//     Route::post('get-calculate-Amount', [BookingController::class, 'calculateAmounts']);
//     Route::get('get-road-sign-dont-have-booking', [RoadSignController::class, 'getRoadsignsDontHaveBooking']);
//     Route::get('get-road-signs-booking-by-week', [RoadSignController::class, 'getRoadsignsBookingByWeek']);
//     Route::get('get-road-signs-template-by-model', [RoadSignController::class, 'getRoadSignsTemplate']);
//     Route::get('get-roadSigns-bookings-by-customer-with-templates-model', [RoadSignController::class, 'getRoadSignsBookingsByCustomerWithTemplatesModel']);
//     Route::get('get-total-payment-and-remaining', [PaymentController::class, 'getTotalPaymentAndRemaining']);
//     Route::get('payment-is-Received/{id}', [PaymentController::class, 'isReceived']);
//     Route::apiResource('companies', CompanyController::class);
// });
// Route::get('give-role', function () {
//     $usersWithoutRoles = User::doesntHave('roles')->get();
//     foreach ($usersWithoutRoles as $user)
//         $user->assignRole('admin');

//     return 'done';
// });


// KHADER