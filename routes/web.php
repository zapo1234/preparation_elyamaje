<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\User;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TiersController;

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

Route::get('/index', function () {
    return redirect()->route('/');
})->name('index');;

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        switch (Auth()->user()->roles->toArray()[0]['id']) {
            case 1 :
                return redirect()->route('admin.analytics');
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
    Route::get("/indexAdmin", [Controller::class, "index"])->name('indexAdmin');
    Route::get("/getAllOrdersAdmin", [Order::class, "getAllOrders"])->name('getAllOrdersAdmin');
    // traiter les routes pour des tiers
    Route::get("/refreshtiers", [TiersController::class, "getiers"])->name('tiers.refreshtiers');
    // mise a jours des tiers via dolibar.
    Route::post("/refreshtiers", [TiersController::class, "postiers"])->name('tiers.refreshtiers');

    Route::get("/configuration", [Controller::class, "configuration"])->name('admin.configuration');
    Route::get("/syncCategories", [Admin::class, "syncCategories"])->name('admin.syncCategories');
    Route::post("/updateOrderCategory", [Admin::class, "updateOrderCategory"])->name('admin.updateOrderCategory');
    Route::get("/analytics", [Admin::class, "analytics"])->name('admin.analytics');
    Route::get("/getAnalytics", [Admin::class, "getAnalytics"])->name('admin.getAnalytics');
});

// PRÉPARATEUR
Route::group(['middleware' => ['auth', 'role:2']], function () {
    Route::get("/orders", [Controller::class, "orderPreparateur"])->name('orders');
    Route::get("/ordersDistributeurs", [Controller::class, "ordersDistributeurs"])->name('orders.distributeurs');
    Route::post("/ordersPrepared", [Order::class, "ordersPrepared"])->name('orders.prepared');
    Route::post("/ordersReset", [Order::class, "ordersReset"])->name('orders.reset');
    Route::get("/ordersHistory", [Order::class, "ordersHistory"])->name('orders.history');
});

// EMBALLEUR
Route::group(['middleware' => ['auth', 'role:3']], function () {
    Route::get("/wrapOrder", [Controller::class, "wrapOrder"])->name('wrapOrder');
    Route::post("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder');
});

// CHEF D'ÉQUIPE
Route::group(['middleware' => ['auth', 'role:4']], function () {
    Route::get("/dashboard", [Controller::class, "dashboard"])->name('leader.dashboard');
    Route::get("/getAllOrders", [Order::class, "getAllOrders"])->name('leader.getAllOrders');
});

// ADMIN ET CHEF D'ÉQUIPE
Route::group(['middleware' =>  ['auth', 'role:1,4']], function () {
    Route::post("/updateRole", [User::class, "updateRole"])->name('updateRole');
    Route::post("/updateAttributionOrder", [Order::class, "updateAttributionOrder"])->name('updateAttributionOrder');
    Route::post("/updateOneOrderAttribution", [Order::class, "updateOneOrderAttribution"])->name('updateOneOrderAttribution');
    Route::get("/distributionOrders", [Order::class, "distributionOrders"])->name('distributionOrders');
    Route::get("/account", [Admin::class, "account"])->name('account');
    Route::post("/account", [User::class, "createAccount"])->name('account.create');
    Route::post("/deleteAccount", [User::class, "deleteAccount"])->name('account.delete');
    Route::post("/updateAccount", [User::class, "updateAccount"])->name('account.update');
    Route::get("/user", [User::class, "getUser"])->name('account.user');

});


// Connexion & Déconnexion
Route::get("/login", [Auth::class, "login"])->name('login');
Route::post("/login", [Auth::class, "postLogin"])->name('login');
Route::get('/logout', [Auth::class, 'logout'])->name('logout');

// Mot de passe oublié et reset mot de passe
Route::get('/authentication-forgot-password', [Auth::class, 'forgotPassword'])->name('authentication-forgot-password');
Route::post('/resetPassword', [Auth::class, 'resetPassword'])->name('password.reset');
Route::get('/authentication-reset-password', [Auth::class, 'resetLinkPage'])->name('auth.passwords.reset');
Route::post('/authentication-reset-password', [Auth::class, 'postResetLinkPage'])->name('auth.passwords.reset');




// Tache crons mise a jours tiers chaque 30minute tous les jours.
Route::get("/imports/tiers/{token}", [TiersController::class, "imports"])->name('imports');


Route::get("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder');
Route::get("/colissimo", [Order::class, "colissimo"])->name('colissimo');







