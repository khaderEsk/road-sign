<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\AuthenticationController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\BookingCustomerController;
use App\Http\Controllers\Api\V1\BrokerController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\Customer\ForgetPasswordCustomerController;
use App\Http\Controllers\Api\V1\Customer\ResetPasswordCustomerController;
use App\Http\Controllers\Api\V1\Customer\RoadSignCustomerController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\ForgetPasswordController;
use App\Http\Controllers\Api\V1\GoogleController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\RoadSignController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TemplateController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\SYRController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer'], function () {
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/verify', [AuthenticationController::class, 'verify']);
    Route::post('/resend-otp', [AuthenticationController::class, 'resendOtp']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    Route::get('/login/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('/login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

    //forget Password
    Route::post('/password/code', [ForgetPasswordCustomerController::class, 'sendResetCode']);
    Route::post('/password/verify', [ForgetPasswordCustomerController::class, 'verifyCode']);
    Route::post('/password/reset', [ForgetPasswordCustomerController::class, 'forgotPassword']);


    Route::apiResource('cities', CityController::class);
    Route::get('/details', [SYRController::class, 'index']);
    Route::get('/roadSing', [RoadSignCustomerController::class, 'getAllRoadSing']);
    Route::get('/RoadSingSites', [RoadSignCustomerController::class, 'RoadSingSites']);
    Route::post('/getRoadSingsFilter', [RoadSignCustomerController::class, 'getRoadSingsFilter']);
    Route::get('/roadSing/{id}', [RoadSignCustomerController::class, 'getById']);
    Route::get('/recommendByLocationAndBudget', [AIController::class, 'recommendByLocationAndBudget']);



    Route::group(['middleware' => ['auth:customer', 'customer.role']], function () {
        Route::get('/refresh', [AuthenticationController::class, 'refresh']);

        Route::apiResource('favorite', FavoriteController::class);

        Route::get('/myProfile', [AuthenticationController::class, 'profile']);
        Route::post('/update-myProfile', [AuthenticationController::class, 'updateProfile']);
        Route::apiResource('booking', BookingCustomerController::class);
        Route::post('get-calculate-Amount', [BookingCustomerController::class, 'calculateAmounts']);
        Route::apiResource('payments', PaymentController::class);
        //Reset Password
        Route::get('/resetPassword', [ResetPasswordCustomerController::class, 'resetPassword']);
        Route::post('/resetPassword/verify', [ResetPasswordCustomerController::class, 'verifyCodeRest']);
        Route::post('/resetPassword/updated', [ResetPasswordCustomerController::class, 'updatedPassword']);
    });
    Route::get('/index', [RoadSignCustomerController::class, 'index']);
});



Route::post('/password/code', [ForgetPasswordController::class, 'sendResetCode']);
Route::post('/password/verify', [ForgetPasswordCustomerController::class, 'verifyCode']);
Route::post('/password/reset', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('templates', TemplateController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('contracts', ContractController::class);
    Route::apiResource('brokers', BrokerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('road-signs', RoadSignController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('bookings', BookingController::class);
    Route::apiResource('cities', CityController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('discounts', DiscountController::class);
    Route::get('get-templates-type', [TemplateController::class, 'getTemplatesType']);
    Route::get('get-templates-model', [TemplateController::class, 'getTemplatesModel']);
    Route::get('get-templates-count-by-model', [TemplateController::class, 'getTemplatesModel']);
    Route::get('summaries', [DashboardController::class, 'summaries']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/get-active-cities', [CityController::class, 'getActive']);
    Route::get('/get-active-regions-by-city', [RegionController::class, 'getActiveByCity']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('get-activities-user-by-id', [ActivityController::class, 'getActivityiesUserById']);
    Route::get('get-template-products', [ProductController::class, 'getTemplateProducts']);
    Route::Post('update-status-order/{id}', [OrderController::class, 'updateStatus']);
    Route::post('get-calculate-Amount', [BookingController::class, 'calculateAmounts']);
    Route::get('get-road-sign-dont-have-booking', [RoadSignController::class, 'getRoadsignsDontHaveBooking']);
    Route::get('get-road-signs-booking-by-week', [RoadSignController::class, 'getRoadsignsBookingByWeek']);
    Route::get('get-road-signs-template-by-model', [RoadSignController::class, 'getRoadSignsTemplate']);
    Route::get('get-roadSigns-bookings-by-customer-with-templates-model', [RoadSignController::class, 'getRoadSignsBookingsByCustomerWithTemplatesModel']);
    Route::get('get-total-payment-and-remaining', [PaymentController::class, 'getTotalPaymentAndRemaining']);
    Route::get('payment-is-Received/{id}', [PaymentController::class, 'isReceived']);
    Route::apiResource('companies', CompanyController::class);

    //Reset Password

    Route::get('/resetPassword', [ResetPasswordController::class, 'resetPassword']);
    Route::post('/resetPassword/verify', [ResetPasswordController::class, 'verifyCodeRest']);
    Route::post('/resetPassword/updated', [ResetPasswordController::class, 'updatedPassword']);
});


Route::get('give-role', function () {
    $usersWithoutRoles = User::doesntHave('roles')->get();
    foreach ($usersWithoutRoles as $user)
        $user->assignRole('admin');

    return 'done';
});


// KHADER