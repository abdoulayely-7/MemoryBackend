<?php

use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\PasswordResetController;
use App\Http\Controllers\api\PersonnelleSanteController;
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

Route::post('/login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('/register',[\App\Http\Controllers\Api\AuthController::class,'register']);

Route::post('/forget-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function ()
{
    Route::get('/user-profile',[AuthController::class,'profile']);
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    // Pour le medecin et le secreatire
    Route::post('/loginPersonnel',[AdminController::class,'loginPersonnel']);
    Route::post('/registerPersonnel',[AdminController::class,'registerPersonnel']);
    Route::put('/user/{id}/update-status', [AdminController::class, 'updateStatusUser']);

});
