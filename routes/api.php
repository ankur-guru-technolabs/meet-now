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
    Route::get('get-user-profile/{id?}', [CustomerController::class,'getProfile'])->name('get-user-profile');
    Route::get('get-filter-data', [CustomerController::class,'getFilterData'])->name('get-filter-data');
    Route::post('update-user-profile', [CustomerController::class,'updateProfile'])->name('update-user-profile');
    Route::post('swipe-profile', [CustomerController::class,'swipeProfile'])->name('swipe-profile');
    Route::post('discover-profile', [CustomerController::class,'discoverProfile'])->name('discover-profile');
    Route::get('matched-user-list', [CustomerController::class,'matchedUserList'])->name('matched-user-list');
    Route::get('chat-list', [CustomerController::class,'chatList'])->name('chat-list');
    Route::post('change-read-status', [CustomerController::class,'changeReadStatus'])->name('change-read-status');
    Route::post('send-message', [CustomerController::class,'sendMessage'])->name('send-message');
    Route::post('unmatch', [CustomerController::class,'unmatch'])->name('unmatch');
    Route::post('report', [CustomerController::class,'report'])->name('report');
    Route::post('contact-support', [CustomerController::class,'contactSupport'])->name('contact-support');
    Route::get('who-viewed-me', [CustomerController::class,'whoViewedMe'])->name('who-viewed-me');
    Route::get('who-likes-me', [CustomerController::class,'whoLikesMe'])->name('who-likes-me');
    Route::get('get-static-page', [CustomerController::class,'staticPage'])->name('get-static-page');
    Route::post('update-location', [CustomerController::class,'updateLocation'])->name('update-location');
	Route::post('single-video-call', [CustomerController::class,'singleVideoCall'])->name('single-video-call');
    Route::post('update-fcm-token', [CustomerController::class,'updateFcmToken'])->name('update-fcm-token');
    Route::get('get-notification-list', [CustomerController::class,'notificationList'])->name('get-notification-list');
    Route::get('notification-read', [CustomerController::class,'notificationRead'])->name('notification-read');
    Route::get('notification-setting', [CustomerController::class,'notificationSetting'])->name('notification-setting');
    Route::get('subscription-list', [CustomerController::class,'subscriptionList'])->name('subscription-list');
    Route::get('subscription-list', [CustomerController::class,'subscriptionList'])->name('subscription-list');
    Route::post('purchase-subscription', [CustomerController::class,'purchaseSubscription'])->name('purchase-subscription');
    Route::get('active-subscription-list', [CustomerController::class,'activeSubscriptionList'])->name('active-subscription-list');
    Route::get('log-out', [CustomerController::class,'logout'])->name('log-out');
});