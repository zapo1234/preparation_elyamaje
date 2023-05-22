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

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        switch (Auth()->user()->role) {
            case 1 :
                return view('index');
                break;
            case 2 :
                return redirect()->route('orders');
                break;
            case 3 :
                return redirect()->route('dashboard.emballeur');
                break;
            default:
                return redirect()->route('logout');
                break;
        } 
        
    })->name('/');
});

// ADMIN
Route::group(['middleware' => ['auth', 'role:1']], function () {
    Route::get("/index", [Controller::class, "index"])->name('orders');
    Route::get("/getAllOrders", [Order::class, "getOrder"])->name('getAllOrders');
    // traiter les routes pour des tiers
    Route::get("refreshtiers", [TiersController::class, "getiers"])->name('tiers.refreshtiers');
    // mise a jours des tiers via dolibar.
    Route::post("refreshtiers", [TiersController::class, "postiers"])->name('tiers.refreshtiers');
});

// PRÉPARATEUR
Route::group(['middleware' => ['auth', 'role:2']], function () {
    Route::get("/orders", [Controller::class, "orderPreparateur"])->name('orders');
    Route::get("/ordersDistributeurs", [Controller::class, "ordersDistributeurs"])->name('orders.distributeurs');
    Route::post("/ordersPrepared", [Order::class, "ordersPrepared"])->name('orders.prepared');
    Route::post("/ordersReset", [Order::class, "ordersReset"])->name('orders.reset');
});

// EMBALLEUR
Route::group(['middleware' => ['auth', 'role:3']], function () {
    Route::get("/dashboard", [Controller::class, "dashboard"])->name('dashboard.emballeur');
});



/*Authentication*/
Route::get("/login", [Auth::class, "login"])->name('login');
Route::post("/login", [Auth::class, "postLogin"])->name('login');
Route::get('/logout', [Auth::class, 'logout'])->name('logout');

// Tâche cron répartition orders
Route::get("/distributionOrders", [Order::class, "distributionOrders"])->name('distributionOrders');
