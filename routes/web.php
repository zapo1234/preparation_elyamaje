<?php

use App\Http\Controllers\Kit;
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
use App\Http\Controllers\SupplierOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
|   Liste des routes de l'application "Préparation" définies selon
|   les rôles utilisateurs ci-dessous.
|
*/

Route::get('/index', function () {
    return redirect()->route('/');
})->name('index');

// Route::get("/preparationCommandeByToken", [Controller::class, "preparationCommandeByToken"])->name('preparationCommandeByToken'); // acces pour preparer la commande doli


Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        if(count(Auth()->user()->roles->toArray()) == 0){
            return redirect()->route('noRole');
        } else {
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
                case 6 :
                    return redirect()->route('labels');
                    break;
                case 8 :
                    return redirect()->route('kit');
                    break;
                default:
                    return redirect()->route('logout');
                    break;
            }
        }
    })->name('/');
});

// ADMIN
Route::group(['middleware' => ['auth', 'role:1']], function () {
    Route::get("/indexAdmin", [Controller::class, "index"])->name('indexAdmin');
    // traiter les routes pour des tiers
    Route::get("/refreshtiers", [TiersController::class, "getiers"])->name('refreshtiers');
    // mise a jours des tiers via dolibar.
    Route::post("/refreshtier", [TiersController::class, "postiers"])->name('refreshtiers');
    // orders facturé via dolibarr
    Route::get("/orderfacturer", [TiersController::class, "getorderfact"])->name('tiers.orderfacturer');
    //traitement ajax des commande facture 
    Route::get("/ordercommande", [TiersController::class, "getidscommande"])->name('tiers.getidscommande');

    // Log des erreurs
    Route::get("/errorLogs", [Admin::class, "errorLogs"])->name('admin.logs'); 
    
    // Transfert
    Route::post("/postReassort", [Controller::class, "postReassort"])->name('postReassort'); 
    Route::post("/delete_transfert/{identifiant}", [Controller::class, "delete_transfert"])->name('delete_transfert'); 
    Route::post("/updateStockWoocommerce/{identifiant}", [Order::class, "updateStockWoocommerce"])->name('updateStockWoocommerce'); 
    Route::post("/cancel_transfert/{identifiant}", [Controller::class, "cancel_transfert"])->name('cancel_transfert');


    Route::get("/executerTransfere/{identifiant_reassort}", [Order::class, "executerTransfere"])->name('executerTransfere');
    // teste 
    Route::get("/actualiseProductDolibarr", [Controller::class, "actualiseProductDolibarr"])->name('actualiseProductDolibarr');
    
    // Route::get("/updateStockWoocommerce", [Order::class, "updateStockWoocommerce"])->name('updateStockWoocommerce');

    //  Route::get("/teste_insert", [Controller::class, "teste_insert"])->name('teste_insert');
    Route::get("/categories", [Controller::class, "categories"])->name('admin.categories');
    Route::get("/syncCategories", [Admin::class, "syncCategories"])->name('admin.syncCategories');

    Route::post("/updateOrderCategory", [Admin::class, "updateOrderCategory"])->name('admin.updateOrderCategory');
    Route::get("/analytics", [Admin::class, "analytics"])->name('admin.analytics');
    Route::get("/getAnalytics", [Admin::class, "getAnalytics"])->name('admin.getAnalytics');
    Route::get("/getAverage", [Admin::class, "getAverage"])->name('admin.getAverage');

    // CRUD Role
    Route::get("/roles", [Admin::class, "roles"])->name('roles');
    Route::post("/roles", [Admin::class, "createRole"])->name('role.create');
    Route::put("/roles", [Admin::class, "updateRole"])->name('role.update');
    Route::delete("/roles", [Admin::class, "deleteRole"])->name('role.delete');

    // Distributeurs
    Route::get("/distributors", [Admin::class, "distributors"])->name('distributors');
    Route::get("/syncDistributors", [Distributors::class, "getAllDistributors"])->name('sync.distributors');

    // Colissimo configuration
    Route::get("/colissimo", [Admin::class, "colissimo"])->name('colissimo');
    Route::post("/colissimo", [Admin::class, "updateColissimo"])->name('colissimo.update');

    // Facturation
    Route::get("/billing", [Admin::class, "billing"])->name('admin.billing');
    Route::post("/billingOrder", [Admin::class, "billingOrder"])->name('admin.billingOrder');
    Route::get("/reinvoice", [Admin::class, "reinvoice"])->name('admin.reinvoice');
    Route::post("/reInvoiceOrder", [Admin::class, "reInvoiceOrder"])->name('admin.reInvoiceOrder');

    // Email preview
    Route::get("/email-preview", [Admin::class, "emailPreview"])->name('email.preview'); 

    // Configuration dolibarr
    Route::get("/configDolibarr", [Admin::class, "configDolibarr"])->name('configDolibarr');
    Route::get("/updatePrepaCategoriesDolibarr", [Admin::class, "updatePrepaCategoriesDolibarr"])->name('updatePrepaCategoriesDolibarr');
    Route::get("/updatePrepaProductsCategories", [Admin::class, "updatePrepaProductsCategories"])->name('updatePrepaProductsCategories');
    Route::get("/updatePrepaProductsAssociation", [Admin::class, "updatePrepaProductsAssociation"])->name('updatePrepaProductsAssociation');
    Route::get("/updatePrepaProductsDolibarr", [Admin::class, "updatePrepaProductsDolibarr"])->name('updatePrepaProductsDolibarr');

    // Beauty Prof détails
    Route::get("/seller", [Admin::class, "seller"])->name('admin.seller'); 
    Route::get("/analyticsSeller", [Admin::class, "analyticsSeller"])->name('analyticsSeller');
    Route::get("/analyticsSellerTotal", [Admin::class, "analyticsSellerTotal"])->name('analyticsSellerTotal');
    Route::get("/cashier", [Admin::class, "cashier"])->name('admin.cashier'); 
    Route::post("/cashMovement", [Admin::class, "cashMovement"])->name('admin.cashMovement'); 
    Route::post("/updateCashMovement", [Admin::class, "updateCashMovement"])->name('admin.updateCashMovement'); 
    Route::post("/cancelCashMovement", [Admin::class, "cancelCashMovement"])->name('admin.cancelCashMovement'); 
    Route::post("/addCashMovement", [Admin::class, "addCashMovement"])->name('admin.addCashMovement'); 

    // gestion de stocks lime et rape.
   Route::get("/stockscat", [Admin::class, "stockscat"])->name('admin.stockscat'); 
    // requete stocks post
   Route::post("/stockscat", [Admin::class, "poststock"])->name('admin.stockscat'); 
    // rape gere le calcule des raps et plaquante
   Route::post("/stockscatrap", [Admin::class, "poststockrap"])->name('admin.stockscatrap'); 
   // traiter les rapes
    // requete stocks post
    Route::get("/stocksrape", [Admin::class, "postrape"])->name('admin.stocksrape'); 
    // généré une facture de la BPP
    Route::get("/generateinvoices", [Admin::class, "generateinvoices"])->name('admin.generateinvoices'); 
    // envoyer une facture BPP
    Route::post("/generateinvoices", [Admin::class, "generatefacture"])->name('admin.generatefacture'); 

    // renvoyer une facture de  bpp 
    Route::post("/generateinvoice", [Admin::class, "generatefactures"])->name('admin.generatefactures'); 
    // Terminaux de paiements
    Route::get("/paymentTerminal", [Admin::class, "paymentTerminal"])->name('admin.paymentTerminal'); 
    Route::post("/addTerminal", [Admin::class, "addTerminal"])->name('terminal.add');
    Route::post("/updateTerminal", [Admin::class, "updateTerminal"])->name('terminal.update');
    Route::post("/deleteTerminal", [Admin::class, "deleteTerminal"])->name('terminal.delete'); 

    // Caisse
    Route::get("/caisse", [Admin::class, "caisse"])->name('admin.caisse'); 
    Route::post("/addCaisse", [Admin::class, "addCaisse"])->name('admin.addCaisse');
    Route::post("/updateCaisse", [Admin::class, "updateCaisse"])->name('admin.updateCaisse');
    Route::post("/deleteCaisse", [Admin::class, "deleteCaisse"])->name('admin.deleteCaisse'); 
});

// PRÉPARATEUR
Route::group(['middleware' => ['auth', 'role:2']], function () {
    Route::get("/orders", [Controller::class, "orderPreparateur"])->name('orders');
    Route::get("/ordersDistributeurs", [Controller::class, "ordersDistributeurs"])->name('orders.distributeurs');
    Route::get("/ordersTransfers", [Controller::class, "ordersTransfers"])->name('orders.transfers');
    Route::post("/transfersProcesssing", [Controller::class, "transfersProcesssing"])->name('orders.transfersProcesssing');
    Route::post("/ordersPrepared", [Order::class, "ordersPrepared"])->name('orders.prepared');

    // Route::post("/transfersPrepared", [Order::class, "transfersPrepared"])->name('transfers.prepared');
    Route::post("/ordersReset", [Order::class, "ordersReset"])->name('orders.reset');
    Route::get("/ordersHistory", [Order::class, "ordersHistory"])->name('orders.history');
    Route::post("/checkProductBarcode", [Order::class, "checkProductBarcode"])->name('orders.checkProductBarcode');
    Route::post("/checkProductBarcodeForTransfers", [Order::class, "checkProductBarcodeForTransfers"])->name('orders.checkProductBarcodeForTransfers'); 
});

// PRÉPARATEUR & VENDEUSE
Route::group(['middleware' => ['auth', 'role:2,8']], function () {
    // Kit prepare
    Route::post("/kitPrepared", [Kit::class, "kitPrepared"])->name('orders.kitPrepared');
    Route::get("/kit", [Kit::class, "kit"])->name('orders.kit');
});


// EMBALLEUR
Route::group(['middleware' => ['auth', 'role:3']], function () {
    Route::get("/wrapOrder", [Controller::class, "wrapOrder"])->name('wrapOrder');
    Route::get("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder');
    Route::get("/checkExpedition", [Order::class, "checkExpedition"])->name('checkExpedition');
});

// CHEF D'ÉQUIPE
Route::group(['middleware' => ['auth', 'role:4']], function () {
    Route::get("/dashboard", [Controller::class, "dashboard"])->name('leader.dashboard');
});

// ADMIN ET CHEF D'ÉQUIPE
Route::group(['middleware' =>  ['auth', 'role:1,4']], function () {
    Route::get("/getAllOrders", [Order::class, "getAllOrders"])->name('getAllOrders');
    Route::post("/updateRole", [User::class, "updateRole"])->name('updateRole');
    Route::post("/updateAttributionOrder", [Order::class, "updateAttributionOrder"])->name('updateAttributionOrder');
    Route::post("/updateOneOrderAttribution", [Order::class, "updateOneOrderAttribution"])->name('updateOneOrderAttribution');
    Route::post("/updateOrderStatus", [Order::class, "updateOrderStatus"])->name('updateOrderStatus');
    Route::post("/orderReInvoicing", [Order::class, "orderReInvoicing"])->name('orderReInvoicing');
    Route::get("/distributionOrders", [Order::class, "distributionOrders"])->name('distributionOrders');
    Route::get("/unassignOrders", [Order::class, "unassignOrders"])->name('unassignOrders');
    Route::get("/account", [Admin::class, "account"])->name('account');
    Route::post("/account", [User::class, "createAccount"])->name('account.create');
    Route::post("/deleteAccount", [User::class, "deleteAccount"])->name('account.delete');
    Route::post("/activeAccount", [User::class, "activeAccount"])->name('account.active');
    Route::post("/updateAccount", [User::class, "updateAccount"])->name('account.update');
    Route::get("/user", [User::class, "getUser"])->name('account.user');
    Route::post("/deleteOrder", [Order::class, "deleteOrder"])->name('deleteOrder');
    Route::post("/deleteOrderProducts", [Order::class, "deleteOrderProducts"])->name('deleteOrderProducts');
    Route::post("/deleteOrderProductsDolibarr", [Order::class, "deleteOrderProductsDolibarr"])->name('deleteOrderProductsDolibarr');
    Route::post("/addOrderProducts", [Order::class, "addOrderProducts"])->name('addOrderProducts');
    Route::post("/closeDay", [Order::class, "closeDay"])->name('leader.closeDay');
    Route::get("/leaderHistoryOrder", [Order::class, "leaderHistoryOrder"])->name('leader.historyOrder');
   
    // CRUD Imprimantes
    Route::get("/printers", [Admin::class, "printers"])->name('printers');
    Route::post("/printers", [Admin::class, "addPrinter"])->name('printer.add');
    Route::post("/updatePrinters", [Admin::class, "updatePrinter"])->name('printer.update');
    Route::post("/deletePrinters", [Admin::class, "deletePrinter"])->name('printer.delete');
    
    // Route pour approvisionnement
    Route::get("/getVieuxSplay", [Controller::class, "getVieuxSplay"])->name('getVieuxSplay');
    Route::post("/createReassort", [Controller::class, "createReassort"])->name('createReassort');
    Route::get('/alertStocks/{idEntrepot}/{Njour}', [Controller::class, 'alertStocks'])->name('alertStocks');
    Route::get('/listeAlerte', [Controller::class, 'listeAlerte'])->name('listeAlerte');
    Route::get('/listeSupplierorders', [SupplierOrderController::class, 'listeSupplierorders'])->name('listeSupplierorders');
    Route::get('/deplacerFichier/{nomFichier}', [Controller::class, 'deplacerFichier'])->name('deplacerFichier');
    Route::post('/updateSessionByNotif', [Controller::class, 'updateSessionByNotif'])->name('updateSessionByNotif');
    Route::post("/updateStockAlertAndStockDesire", [Controller::class, "updateStockAlertAndStockDesire"])->name('updateStockAlertAndStockDesire');

    // Missing Labels
    Route::get("/missingLabels", [Admin::class, "missingLabels"])->name('missingLabels');
    Route::post("/validLabelMissing", [Admin::class, "validLabelMissing"])->name('validLabelMissing');
    Route::post("/cancelLabelMissing", [Admin::class, "cancelLabelMissing"])->name('cancelLabelMissing');

    Route::post("/changeUserForReassort", [Controller::class, "changeUserForReassort"])->name('changeUserForReassort');
    // Route créate kits
    Route::post("/constructKit", [Order::class, "constructKit"])->name('constructKit');
    Route::post("/validateKits", [Order::class, "validateKits"])->name('validateKits');

    // Téléchargement de fichier de réassort
    Route::post("/uploadFile", [Order::class, "uploadFile"])->name('uploadFile');

    // ajax verification des commandes api dolibar factures.
    Route::get("/orderinvoices", [TiersController::class, "getinvoices"])->name('tiers.getinvoices');

    // Lyes
    Route::post('/initialQtyLot', [Admin::class, 'initialQtyLot'])->name('initialQtyLot');
    Route::post('/updateProducts', [Admin::class, 'updateProducts'])->name('updateProducts');

    // Products
    Route::get("/products", [Controller::class, "products"])->name('admin.products');
    Route::get("/syncProducts", [Admin::class, "syncProducts"])->name('admin.syncProducts');
    Route::post("/products", [Admin::class, "updateProduct"])->name('update.product');
    Route::post("/productsMultiple", [Admin::class, "updateProductsMultiple"])->name('admin.updateProductsMultiple');
});

// ADMIN - CHEF D'ÉQUIPE ET EMBALLEUR
Route::group(['middleware' =>  ['auth', 'role:1,4,3,6']], function () {
    Route::get("/labels", [Label::class, "getlabels"])->name('labels');
    Route::post("/labels", [Label::class, "getlabels"])->name('labels.filter');
    Route::post("/labelDownload", [Label::class, "labelDownload"])->name('label.download');
    Route::post("/labelPrintZPL", [Label::class, "labelPrintZPL"])->name('label.printZpl');
    Route::post("/labelShow", [Label::class, "labelShow"])->name('label.show');
    Route::post("/generateLabel", [Label::class, "generateLabel"])->name('label.generate');
    Route::post("/labelDelete", [Label::class, "labelDelete"])->name('label.delete');
    Route::post("/labelDownloadCn23", [Label::class, "labelDownloadCn23"])->name('label.download_cn23');
    Route::get("/bordereaux", [Label::class, "bordereaux"])->name('bordereaux');
    Route::post("/generateBordereau", [Label::class, "generateBordereau"])->name('bordereau.generate');
    Route::post("/bordereauPDF", [Label::class, "bordereauPDF"])->name('bordereau.download');
    Route::post("/bordereauDelete", [Label::class, "bordereauDelete"])->name('bordereau.delete');
    Route::post("/getProductOrderLabel", [Label::class, "getProductOrderLabel"])->name('label.product_order_label');

    Route::post("/missingProductReassort", [Order::class, "missingProductReassort"])->name('missingProductReassort');
});

// ADMIN - CHEF D'ÉQUIPE & SAV
Route::group(['middleware' => ['auth', 'role:1,4,6']], function () {
    Route::get("/sav", [Controller::class, "sav"])->name('sav');
    Route::get("/getDetailsOrder", [Order::class, "getDetailsOrder"])->name('getDetailsOrder');
    // Update details order billing and shipping
    Route::post("/updateDetailsOrders", [Order::class, "updateDetailsOrders"])->name('updateDetailsOrders');
    Route::post("/generateHistory", [Order::class, "generateHistory"])->name('history.generate');
    Route::get("/leaderHistory", [Order::class, "leaderHistory"])->name('leader.history');
    Route::get("/ordersDetails", [Order::class, "ordersDetails"])->name('ordersDetails');
    Route::get("/cashierWaiting", [Admin::class, "cashierWaiting"])->name('admin.cashierWaiting'); 
    Route::get("/beautyProfHistory", [Admin::class, "beautyProfHistory"])->name('admin.beautyProfHistory'); 

    Route::post("/returnOrder", [Order::class, "returnOrder"])->name('returnOrder'); 

});

// ADMIN - CHEF D'ÉQUIPE & EMBALLEUR & SAV
Route::group(['middleware' => ['auth', 'role:1,4,3,6']], function () {
    Route::post("/getTrackingStatus", [Order::class, "getTrackingStatus"])->name('getTrackingStatus');
});


// Vendeuse
Route::group(['middleware' =>  ['auth', 'role:8']], function () {
    // Route::get("/shop", [Controller::class, "shop"])->name('shop');
});

// TOUS LES ROLES
Route::group(['middleware' =>  ['auth']], function () {
    Route::get("/notifications", [Notification::class, "notificationRead"])->name('notification.read');
    Route::get("/allNotification", [Notification::class, "allNotification"])->name('notifications.all');

    // Détails users
    Route::get("/accountDetails", [User::class, "accountDetails"])->name('admin.accountDetails');

    // Update data user
    Route::post("/updateImageProfil", [User::class, "updateImageProfil"])->name('updateImageProfil');
    Route::post("/updateAccountDetails", [User::class, "updateAccountDetails"])->name('updateAccountDetails');
});

// ROLES NON DÉFINI 
Route::group(['middleware' =>  ['auth', 'role:5']], function () {
    Route::get("/noRole", [User::class, "noRole"])->name('noRole');
});

// ROUTES NON AUTHENTIFIÉES

// Connexion & Déconnexion
Route::get("/login", [Auth::class, "login"])->name('login');
Route::post("/login", [Auth::class, "postLogin"])->name('login');
Route::get('/logout', [Auth::class, 'logout'])->name('logout');

// Mot de passe oublié et reset mot de passe
Route::get('/authentication-forgot-password', [Auth::class, 'forgotPassword'])->name('authentication-forgot-password');
Route::post('/resetPassword', [Auth::class, 'resetPassword'])->name('password.reset');
Route::get('/authentication-reset-password', [Auth::class, 'resetLinkPage'])->name('auth.passwords.reset');
Route::post('/authentication-reset-password', [Auth::class, 'postResetLinkPage'])->name('auth.passwords.reset');

// TACHES CRONS

// Mise a jours tiers chaque 30minute tous les jours.
Route::get("/imports/tiers/{token}", [TiersController::class, "imports"])->name('imports');

// Récupère les commandes avec carte cadeaux seulement
Route::get("/giftCardOrders/{token}", [Controller::class, "giftCardOrders"]);

// Alerte Stock 
Route::get('/alerteStockCron/{token}', [Controller::class, 'alerteStockCron'])->name('alerteStockCron');


// Route test validation emballage à enlever par la suite
Route::get("/validWrapOrder", [Order::class, "validWrapOrder"])->name('validWrapOrder');
Route::get("/bordereauChrono", [Controller::class, "bordereauChrono"])->name('bordereauChrono');

// Get orders WordPress
Route::get("/getOrders/{token}", [Order::class, "getOrders"])->name('getOrders');

// Route::get("/syncHistoriesTotalProduct", [Order::class, "syncHistoriesTotalProduct"])->name('syncHistoriesTotalProduct');

// DEPLACER SUR CRON ELYAMAJE FR
// Route::get("/trackingLabelStatus/{token}", [Label::class, "getTrackingLabelStatus"])->name('label.tracking');
