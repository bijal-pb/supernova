<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceItemController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/privacy-policy',function () {
    return view("admin.privacy-policy");
}); 
Route::get('/terms',function () {
    return view("admin.terms");
});

Route::get('/about-us',function () {
    return view("admin.about");
});
Route::get('/token/{id}', [HomeController::class, 'accessToken'])->name('authtoken');

Route::get('/', function () {
    return redirect("/admin");
});


Auth::routes();

Route::get('/home', function () {
    return redirect("/admin");
});

Route::get('/forgot/password', [UserController::class, 'forgot_password'])->name('admin.forgot');
Route::post('/forgot/password/mail', [UserController::class, 'password_mail'])->name('admin.forgot.mail');
Route::post('admin/login', [UserController::class, 'admin_login'])->name('admin.login');

Route::name('admin.')->namespace('Admin')->group(function () {
    Route::group(['prefix' => 'admin', 'middleware' => ['admin.check']], function () {
        Route::get('/', [AdminController::class, 'index'])->name('home');
       
        // users  route
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::get('/password', [UserController::class, 'password'])->name('password');
        Route::post('/password/change', [UserController::class, 'change_password'])->name('password.update');
        Route::post('/profile/update', [UserController::class, 'update_profile'])->name('profile.update');
        Route::get('/users', [UserController::class, 'index'])->name('user');
        Route::get('/users/list', [UserController::class, 'users'])->name('users.list');
        Route::get('/get/user', [UserController::class, 'getUser'])->name('user.get');
        Route::get('/user/status/change', [UserController::class, 'changeStatus'])->name('user.status.change');
        Route::post('/user/store', [UserController::class, 'store'])->name('user.store');

        // app setting
        Route::get('setting', [UserController::class, 'app_setting'])->name('setting');
        Route::post('setting/update', [UserController::class, 'setting_update'])->name('setting.update');

         //customer route
         Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
         Route::get('/customer/get', [CustomerController::class, 'getCustomer'])->name('customer.get');
         Route::post('/customer/store', [CustomerController::class, 'store'])->name('customer.store');
         Route::post('/customer/delete', [CustomerController::class, 'delete'])->name('customer.delete');
         Route::get('/customer/list', [CustomerController::class, 'customers'])->name('customer.list');
         Route::get('/customer/status/change', [CustomerController::class, 'changeStatus'])->name('customer.status.change');

         //staff route
         Route::get('/staff', [StaffController::class, 'index'])->name('staff');
         Route::get('/staff/get', [StaffController::class, 'getStaff'])->name('staff.get');
         Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
         Route::post('/staff/delete', [StaffController::class, 'delete'])->name('staff.delete');
         Route::get('/staff/list', [StaffController::class, 'staffs'])->name('staff.list');
         Route::get('/staff/status/change', [StaffController::class, 'changeStatus'])->name('staff.status.change');

         // category routes
        Route::get('/category', [CategoryController::class, 'index'])->name('category');
        Route::get('/category/get', [CategoryController::class, 'getCategory'])->name('category.get');
        Route::post('/category/store', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/category/list', [CategoryController::class, 'categories'])->name('category.list');
        Route::get('/category/status/change', [CategoryController::class, 'changeStatus'])->name('category.status.change');

        // Service routes
        Route::get('/service', [ServiceController::class, 'index'])->name('service');
        Route::get('/service/get', [ServiceController::class, 'getService'])->name('service.get');
        Route::post('/service/store', [ServiceController::class, 'store'])->name('service.store');
        Route::post('/service/delete', [ServiceController::class, 'delete'])->name('service.delete');
        Route::get('/service/list', [ServiceController::class, 'services'])->name('service.list');
        Route::get('/service/status/change', [ServiceController::class, 'changeStatus'])->name('service.status.change');
        Route::get('/service/get/list', [ServiceController::class, 'service_list'])->name('service.get.list');

         // Service Item routes
         Route::get('/service_item', [ServiceItemController::class, 'index'])->name('service_item');
         Route::get('/service_item/get', [ServiceItemController::class, 'getServiceItem'])->name('service_item.get');
         Route::post('/service_item/store', [ServiceItemController::class, 'store'])->name('service_item.store');
         Route::post('/service_item/delete', [ServiceItemController::class, 'delete'])->name('service_item.delete');
         Route::get('/service_item/list', [ServiceItemController::class, 'service_items'])->name('service_item.list');
         Route::get('/service_item/status/change', [ServiceItemController::class, 'changeStatus'])->name('service_item.status.change');
    });
});

Route::get('logout', [LoginController::class, 'logout'])->name('logout');
