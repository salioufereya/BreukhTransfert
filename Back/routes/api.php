<?php

use App\Http\Controllers\CompteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TransactionController;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get("/clients/{keySearch}", [ClientController::class, "getClientByTelOrNumCompte"]);

Route::post("/transactions/Transfert", [TransactionController::class, "transfert"]);
Route::post("/transactions/Dépot", [TransactionController::class, "depot"]);
Route::post("/transactions/historique", [TransactionController::class, "historique"]);


Route::post("/transactions/getMontant", [TransactionController::class, "getMontant"]);

Route::post("/transactions/Retrait", [TransactionController::class, "retrait"]);
Route::post("/transactions/Retrait_Avec_Code", [TransactionController::class, "retraitAvecCode"]);
Route::post("/User/Bloquer", [CompteController::class, "bloque"]);
Route::post("/User/Debloquer", [CompteController::class, "debloque"]);

Route::post("/User/Fermer", [CompteController::class, "ferme"]);

Route::post("/Client/add", [ClientController::class, "addClient"]);

Route::post("/Compte/add", [CompteController::class, "addCompte"]);

Route::post("/Compte", [CompteController::class, "compte"]);


Route::post("/Compte/Annuler", [TransactionController::class, "annulerTransaction"]);