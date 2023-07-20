<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\User;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Label;
use App\Http\Controllers\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Distributors;
use App\Http\Controllers\Notification;
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
})->name('index');

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
            case 5 :
                return redirect()->route('noRole');
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
    // traiter les routes pour des tiers
    Route::get("/refreshtiers", [TiersController::class, "getiers"])->name('tiers.refreshtiers');
    // mise a jours des tiers via dolibar.
    Route::post("/refreshtiers", [TiersController::class, "postiers"])->name('tiers.refreshtiers');

    Route::get("/categories", [Controller::class, "categories"])->name('admin.categories');
    Route::get("/products", [Controller::class, "products"])->name('admin.products');
    Route::get("/syncCategories", [Admin::class, "syncCategories"])->name('admin.syncCategories');
    Route::get("/syncProducts", [Admin::class, "syncProducts"])->name('admin.syncProducts');
    Route::post("/products", [Admin::class, "updateProduct"])->name('update.product');
    Route::post("/updateOrderCategory", [Admin::class, "updateOrderCategory"])->name('admin.updateOrderCategory');
    Route::get("/analytics", [Admin::class, "analytics"])->name('admin.analytics');

    // CRUD Role
    Route::get("/roles", [Admin::class, "roles"])->name('roles');
    Route::post("/roles", [Admin::class, "createRole"])->name('role.create');
    Route::put("/roles", [Admin::class, "updateRole"])->name('role.update');
    Route::delete("/roles", [Admin::class, "deleteRole"])->name('role.delete');

    // Distributeurs
    Route::get("/distributors", [Admin::class, "distributors"])->name('distributors');
    Route::get("/syncDistributors", [Distributors::class, "getAllDistributors"])->name('sync.distributors');

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
    Route::get("/checkExpedition", [Order::class, "checkExpedition"])->name('checkExpedition');
});

// CHEF D'ÉQUIPE
Route::group(['middleware' => ['auth', 'role:4']], function () {
    Route::get("/dashboard", [Controller::class, "dashboard"])->name('leader.dashboard');
    Route::get("/leaderHistory", [Order::class, "leaderHistory"])->name('leader.history');
    Route::post("/generateHistory", [Order::class, "generateHistory"])->name('history.generate');
    Route::get("/leaderHistoryOrder", [Order::class, "leaderHistoryOrder"])->name('leader.historyOrder');
});

// ADMIN ET CHEF D'ÉQUIPE
Route::group(['middleware' =>  ['auth', 'role:1,4']], function () {
    Route::get("/getAllOrders", [Order::class, "getAllOrders"])->name('getAllOrders');
    Route::post("/updateRole", [User::class, "updateRole"])->name('updateRole');
    Route::post("/updateAttributionOrder", [Order::class, "updateAttributionOrder"])->name('updateAttributionOrder');
    Route::post("/updateOneOrderAttribution", [Order::class, "updateOneOrderAttribution"])->name('updateOneOrderAttribution');
    Route::post("/updateOrderStatus", [Order::class, "updateOrderStatus"])->name('updateOrderStatus');
    Route::get("/distributionOrders", [Order::class, "distributionOrders"])->name('distributionOrders');
    Route::get("/account", [Admin::class, "account"])->name('account');
    Route::post("/account", [User::class, "createAccount"])->name('account.create');
    Route::post("/deleteAccount", [User::class, "deleteAccount"])->name('account.delete');
    Route::post("/updateAccount", [User::class, "updateAccount"])->name('account.update');
    Route::get("/user", [User::class, "getUser"])->name('account.user');
    Route::post("/deleteOrderProducts", [Order::class, "deleteOrderProducts"])->name('deleteOrderProducts');
    Route::post("/addOrderProducts", [Order::class, "addOrderProducts"])->name('addOrderProducts');
    Route::post("/closeDay", [Order::class, "closeDay"])->name('leader.closeDay');
});

// ADMIN - CHEF D'ÉQUIPE ET EMBALLEUR
Route::group(['middleware' =>  ['auth', 'role:1,4,3']], function () {
    Route::get("/labels", [Label::class, "getlabels"])->name('labels');
    Route::post("/labelPDF", [Label::class, "labelPDF"])->name('label.download');
    Route::post("/labelShow", [Label::class, "labelShow"])->name('label.show');
    Route::post("/generateLabel", [Label::class, "generateLabel"])->name('label.generate');
    Route::post("/labelDelete", [Label::class, "labelDelete"])->name('label.delete');
    Route::get("/bordereaux", [Label::class, "bordereaux"])->name('bordereaux');
    Route::post("/generateBordereau", [Label::class, "generateBordereau"])->name('bordereau.generate');
    Route::post("/bordereauPDF", [Label::class, "bordereauPDF"])->name('bordereau.download');
    Route::post("/bordereauDelete", [Label::class, "bordereauDelete"])->name('bordereau.delete');
    Route::post("/getProductOrderLabel", [Label::class, "getProductOrderLabel"])->name('label.product_order_label');
});

// TOUS LES ROLES
Route::group(['middleware' =>  ['auth']], function () {
    Route::get("/notifications", [Notification::class, "notificationRead"])->name('notification.read');
    Route::get("/allNotification", [Notification::class, "allNotification"])->name('notifications.all');
});

// ROLES NON DÉFINI 
Route::group(['middleware' =>  ['auth', 'role:5']], function () {
    Route::get("/noRole", [User::class, "noRole"])->name('noRole');
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

// Route test à enlever par la suite
Route::get("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder'); 

// Email preview
Route::get("/email-preview", [Admin::class, "emailPreview"])->name('email.preview'); 
