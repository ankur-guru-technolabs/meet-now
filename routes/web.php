<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Auth;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [LoginController::class, 'showLoginForm'])->name('/');
Route::post('/login-admin', [LoginController::class, 'login'])->name('login-admin');
Route::get('privacy-policy', [LoginController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('terms-condition', [LoginController::class, 'termsCondition'])->name('terms-condition');
Route::get('contact', [LoginController::class, 'contactForm'])->name('contact');
Route::post('contact-us-store', [LoginController::class, 'contactStore'])->name('contact-us-store');

Route::middleware(['admin'])->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::group(['prefix' => 'questions','as'=>'questions.'], function () {
        Route::group(['prefix' => 'bodytype','as'=>'bodytype.'], function () {
            Route::get('list', [AdminController::class, 'bodytypeList'])->name('list');
            Route::post('store', [AdminController::class, 'bodytypeStore'])->name('store');
            Route::post('update', [AdminController::class, 'bodytypeUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'bodytypeDelete'])->name('delete');
        });
        Route::group(['prefix' => 'education','as'=>'education.'], function () {
            Route::get('list', [AdminController::class, 'educationList'])->name('list');
            Route::post('store', [AdminController::class, 'educationStore'])->name('store');
            Route::post('update', [AdminController::class, 'educationUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'educationDelete'])->name('delete');
        });
        Route::group(['prefix' => 'exercise','as'=>'exercise.'], function () {
            Route::get('list', [AdminController::class, 'exerciseList'])->name('list');
            Route::post('store', [AdminController::class, 'exerciseStore'])->name('store');
            Route::post('update', [AdminController::class, 'exerciseUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'exerciseDelete'])->name('delete');
        });
        Route::group(['prefix' => 'gender','as'=>'gender.'], function () {
            Route::get('list', [AdminController::class, 'genderList'])->name('list');
            Route::post('store', [AdminController::class, 'genderStore'])->name('store');
            Route::post('update', [AdminController::class, 'genderUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'genderDelete'])->name('delete');
        });
        Route::group(['prefix' => 'hobby','as'=>'hobby.'], function () {
            Route::get('list', [AdminController::class, 'hobbyList'])->name('list');
            Route::post('store', [AdminController::class, 'hobbyStore'])->name('store');
            Route::post('update', [AdminController::class, 'hobbyUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'hobbyDelete'])->name('delete');
        });
        Route::group(['prefix' => 'religion','as'=>'religion.'], function () {
            Route::get('list', [AdminController::class, 'religionList'])->name('list');
            Route::post('store', [AdminController::class, 'religionStore'])->name('store');
            Route::post('update', [AdminController::class, 'religionUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'religionDelete'])->name('delete');
        });
    });
    
    Route::group(['prefix' => 'users','as'=>'users.'], function () {
        Route::get('list', [UserController::class, 'list'])->name('list');
        Route::post('status/update', [UserController::class, 'updateStatus'])->name('status-update');
    });
    
    Route::group(['prefix' => 'feedback','as'=>'feedback.'], function () {
        Route::get('list', [AdminController::class, 'feedbackList'])->name('list');
    });
    
    Route::group(['prefix' => 'static-pages','as'=>'static-pages.'], function () {
        Route::get('list', [AdminController::class, 'staticPagesList'])->name('list');
        Route::get('page-edit/{id}', [AdminController::class, 'pageEdit'])->name('page-edit');
        Route::post('page-update', [AdminController::class, 'pageUpdate'])->name('page-update');
    });

    Route::group(['prefix' => 'notification','as'=>'notification.'], function () {
        Route::get('index', [AdminController::class, 'notificationIndex'])->name('index');
        Route::post('send', [AdminController::class, 'notificationSend'])->name('send');
    });
    
    Route::group(['prefix' => 'subscription','as'=>'subscription.'], function () {
        Route::get('order', [AdminController::class, 'subscriptionOrder'])->name('order');
        Route::get('list', [AdminController::class, 'subscriptionList'])->name('list');
        Route::get('subscription-edit/{id}', [AdminController::class, 'subscriptionEdit'])->name('subscription-edit');
        Route::post('subscription-update', [AdminController::class, 'subscriptionUpdate'])->name('subscription-update');
    });
    
    Route::group(['prefix' => 'report','as'=>'report.'], function () {
        Route::get('list', [AdminController::class, 'reportList'])->name('list');
        Route::post('user-block', [AdminController::class, 'userBlock'])->name('user-block');
    });

});

Route::get('/subscription-expire', [LoginController::class, 'subscriptionExpire'])->name('cron');
Route::get('/message-delete', [LoginController::class, 'messageDelete'])->name('cron');
Route::get('/apple-plan-status-check', [LoginController::class, 'applePlanStatusCheck'])->name('cron');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Auth::routes();

Route::post('/logout', function () {
    Auth::logout();

    Session::forget('session_start_time');
    Session::forget('session_lifetime');
    
    return redirect('/');
})->name('logout');
