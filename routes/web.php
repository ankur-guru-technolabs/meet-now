<?php

use Illuminate\Support\Facades\Route;
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

Route::middleware(['admin'])->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::group(['prefix' => 'questions','as'=>'questions.'], function () {
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

});


// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Auth::routes();

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
