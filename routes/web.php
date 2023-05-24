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
        switch (Auth()->user()->role_id) {
            case 1 :
                return view('index');
                break;
            case 2 :
                return redirect()->route('orders');
                break;
            case 3 :
                return redirect()->route('wrapOrder');
                break;
            case 4 :
                return redirect()->route('leader.dashboard');
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
    Route::get("/getAllOrdersAdmin", [Order::class, "getOrder"])->name('getAllOrdersAdmin');
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
    Route::get("/wrapOrder", [Controller::class, "wrapOrder"])->name('wrapOrder');
    Route::post("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder');
});

// CHEF D'ÉQUIPE
Route::group(['middleware' => ['auth', 'role:4']], function () {
    Route::get("/dashboard", [Controller::class, "dashboard"])->name('leader.dashboard');
    Route::post("/updateRole", [Controller::class, "updateRole"])->name('updateRole');
    Route::get("/getAllOrders", [Order::class, "getOrder"])->name('leader.getAllOrders');
    Route::post("/updateAttributionOrder", [Order::class, "updateAttributionOrder"])->name('updateAttributionOrder');
    Route::post("/updateOneOrderAttribution", [Order::class, "updateOneOrderAttribution"])->name('updateOneOrderAttribution');
});



/*Authentication*/
Route::get("/login", [Auth::class, "login"])->name('login');
Route::post("/login", [Auth::class, "postLogin"])->name('login');
Route::get('/logout', [Auth::class, 'logout'])->name('logout');

// Tâche cron répartition orders
Route::get("/distributionOrders", [Order::class, "distributionOrders"])->name('distributionOrders');

// tache cron import de tiers toute les minutes....
Route::get("/importiers/{token}", [Order::class, "importiers"])->name('importiers');



Route::get("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder');


