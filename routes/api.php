<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// http://localhost/preparation.elyamaje.com/preparationCommandeByToken
Route::get("/preparationCommandeByToken", [Controller::class, "preparationCommandeByToken"])->name('preparationCommandeByToken'); // acces pour preparer la commande doli

// Route::group(['middleware' =>  ['auth:sanctum']], function () {
// });
Route::post("/login", [ApiController::class, "login"]);



// Route api mise à jour d'étiquettes
Route::get("/getLabels", [ApiController::class, "getLabels"]);
Route::post("/updateLabelsStatus", [ApiController::class, "updateLabelsStatus"]);







