<?php

use App\Http\Controllers\EmailVerfifiedController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use PhpParser\Node\Stmt\For_;

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


Route::prefix("google")->group(function () {
    Route::get("/auth/redirect", [GoogleController::class, "authRedirect"])->name("google.auth");
    Route::get("/auth/callback", [GoogleController::class, "authCallback"]);
});



Route::prefix("facebook")->group(function () {
    Route::get("/auth/redirect", [GoogleController::class, "authRedirect"])->name("facebook.auth");
    Route::get("/auth/callback", [GoogleController::class, "authCallback"]);
});

Route::post("/new-user", [LoginController::class, "newUser"]);

Route::get('/email/verify', [EmailVerfifiedController::class, "verifyNotification"])->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [EmailVerfifiedController::class, "verifyFromEmail"])->middleware(['auth', 'signed'])->name('verification.verify');

Route::get("login", [LoginController::class, "index"])->name("login");

Route::post("/app/login", [LoginController::class, "login"]);

Route::post('/email/verification-notification', [EmailVerfifiedController::class, "verificationNotification"])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/logout', [LoginController::class, "logout"])->middleware(['auth'])->name('logout');


Route::get('/forgot-password', [ForgotPasswordController::class, "forgotPassword"])->middleware('guest')->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, "updatePassword"])->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', [ForgotPasswordController::class, "resetPasswordView"])->middleware('guest')->name('password.reset');


Route::post('/reset-password', [ForgotPasswordController::class, "resetPassword"])->middleware('guest')->name('password.update');
