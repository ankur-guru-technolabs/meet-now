<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;

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

Route::post('send-otp', [AuthController::class, 'sendOtp'])->name('send-otp');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
Route::get('get-registration-form-data', [AuthController::class, 'getRegistrationFormData'])->name('get-registration-form-data');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('email-exist', [AuthController::class, 'emailExist'])->name('email-exist');
 
Route::middleware('auth:api')->group(function () {
    Route::get('get-user-profile/{id}', [CustomerController::class,'getProfile'])->name('get-user-profile');
    Route::get('get-filter-data', [CustomerController::class,'getFilterData'])->name('get-filter-data');
    Route::post('update-user-profile', [CustomerController::class,'updateProfile'])->name('update-user-profile');
    Route::post('swipe-profile', [CustomerController::class,'swipeProfile'])->name('swipe-profile');
    Route::post('discover-profile', [CustomerController::class,'discoverProfile'])->name('discover-profile');
    Route::post('update-location', [CustomerController::class,'updateLocation'])->name('update-location');
    Route::get('log-out', [CustomerController::class,'logout'])->name('log-out');
});