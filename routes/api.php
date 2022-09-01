<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\SubServiceController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('username/check', [UserController::class, 'check_username']);
Route::post('forgot/password', [UserController::class,'forgot_password']);
Route::post('update/password', [UserController::class,'update_password']);

Route::group(['middleware' => ['apilogs','auth:api']], function () {
    // profile
    Route::get('profile', [UserController::class, 'me']);
    Route::post('profile/edit/image', [UserController::class, 'edit_profile_image']);
    Route::post('profile/edit', [UserController::class, 'edit_profile']);
    Route::post('change/password', [UserController::class, 'change_password']);
    Route::get('employee/list', [UserController::class, 'employee_list']);
    Route::post('employee/add/edit', [UserController::class, 'employee_add_edit']);
    Route::post('employee/delete', [UserController::class, 'employee_delete']);

    //staff home
    Route::get('staff/home', [HomeController::class, 'staff_home']);
    Route::get('staff/appointment', [OrderController::class, 'staffAppointment']);


    //Customer home
    Route::get('customer/home', [HomeController::class, 'customer_home']);
    Route::get('customer/appointment', [OrderController::class, 'customerAppointment']);

     //business home
     Route::get('business/home', [HomeController::class, 'business_home']);
     Route::get('business/appointment', [OrderController::class, 'businessAppointment']);
     Route::post('business/order/accept', [OrderController::class, 'BusinessOrderAccept']);
     Route::post('business/order/update', [OrderController::class, 'business_order_update']);



    //Service
    Route::get('get/services', [ServiceController::class, 'get_services']);

    //sub-service Or serviceItem
    Route::get('get/subservices', [SubServiceController::class, 'get_sub_services']);

    //order
    Route::post('order/place', [OrderController::class, 'order_place']);
    Route::get('order/detail', [OrderController::class, 'order_detail']);
    Route::post('customer/order/accept', [OrderController::class, 'CustomerOrderAccept']);
    Route::post('order/status/update', [OrderController::class, 'orderStatusUpdate']);
    Route::post('order/image', [OrderController::class, 'order_image']);

    //notification
    Route::get('notification/enable', [NotificationController::class,'notification_enable']);
    Route::get('notifications', [NotificationController::class,'notifications']);
    Route::get('read/notifications', [NotificationController::class,'read_notifications']);


    //payment
    Route::post('customer/payment', [PaymentController::class, 'customer_payment']);

    //logout
    Route::get('logout', [UserController::class, 'logout']);
});
