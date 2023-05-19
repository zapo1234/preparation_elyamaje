<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order;
use App\Http\Controllers\TiersController;
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
    return view('index');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get("/index", [Controller::class, "index"])->name('orders');
    Route::get("/getOrder", [Order::class, "getOrder"])->name('getOrder');

    // traiter les routes pour des tiers
     Route::get("/refreshtiers", [TiersController::class, "getiers"])->name('refreshtiers');
     // mise a jours des tiers via dolibar.
     Route::post("/refreshtiers", [TiersController::class, "postiers"]);
});


/*Authentication*/
Route::get("/authentication-signin", [Auth::class, "login"])->name('login');
Route::post("/authentication-signin", [Auth::class, "postLogin"])->name('login');
Route::get('/logout', [Auth::class, 'logout'])->name('logout');

// Tâche cron répartition orders
Route::get("/distributionOrders", [Order::class, "distributionOrders"])->name('distributionOrders');
