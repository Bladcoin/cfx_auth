<?php

use App\Http\Controllers\GoogleController;
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
