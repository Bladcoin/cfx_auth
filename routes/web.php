<?php

use App\Http\Controllers\EmailVerfifiedController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

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


Route::prefix("google")->group(function() {
    Route::get("/auth/redirect", [GoogleController::class, "authRedirect"])->name("google.auth");
    Route::get("/auth/callback", [GoogleController::class, "authCallback"]);
});



Route::prefix("facebook")->group(function() {
    Route::get("/auth/redirect", [GoogleController::class, "authRedirect"])->name("facebook.auth");
    Route::get("/auth/callback", [GoogleController::class, "authCallback"]);
});

Route::post("/new-user", [LoginController::class, "newUser"]);

Route::get('/email/verify', [EmailVerfifiedController::class, "verifyNotification"])->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [EmailVerfifiedController::class, "verifyFromEmail"])->middleware(['auth', 'signed'])->name('verification.verify');

Route::get("login", [LoginController::class, "index"])->name("login");

Route::post("/app/login", [LoginController::class, "login"]);

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/logout', [LoginController::class, "logout"])->middleware(['auth'])->name('logout');



