<?php


use App\Http\Controllers\api\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AdminController;

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

Route::get('/specialites',[\App\Http\Controllers\Api\AdminController::class,'getSpecialites']);
Route::get('/medecins', [\App\Http\Controllers\Api\AuthController::class, 'search']);

//Route::post('/forget-password', [PasswordResetController::class, 'sendResetLinkEmail']);
//Route::post('/reset-password', [PasswordResetController::class, 'reset']);
Route::get('forgot-password', [PasswordResetController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

// Route pour envoyer le lien de réinitialisation
Route::post('/forgot-password', [PasswordResetController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Route pour afficher le formulaire de réinitialisation de mot de passe


Route::post('/password-reset', [\App\Http\Controllers\Api\NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');


Route::middleware('auth:api')->group(function ()
{
    Route::get('/user-profile',[\App\Http\Controllers\Api\AuthController::class,'profile']);
    Route::post('/logout',[\App\Http\Controllers\Api\AuthController::class,'logout'])->name('logout');
    // Pour le medecin et le secreatire
    Route::post('/loginPersonnel',[\App\Http\Controllers\Api\AdminController::class,'loginPersonnel']);
    Route::post('/registerPersonnel',[\App\Http\Controllers\Api\AdminController::class,'registerPersonnel']);
    Route::put('/user/{id}/update-status', [\App\Http\Controllers\Api\AdminController::class, 'updateStatusUser']);

});


// Routes pour la gestion du personnel de santé
Route::prefix('/admin')->group(function () {
    // Enregistrement du personnel
    Route::post('/register-personnel', [AdminController::class, 'registerPersonnel']);

    // Connexion du personnel
    Route::post('/login-personnel', [AdminController::class, 'loginPersonnel']);

    // Obtenir tous les personnels
    Route::get('/personnel', [AdminController::class, 'getPersonnel']);
    Route::get('/allpersonnel', [AdminController::class, 'getAllPersonnel']);

    // Obtenir un personnel par ID
    Route::get('personnel/{id}', [AdminController::class, 'getPersonnelById']);

    // Mettre à jour un personnel par ID
    Route::put('personnel/{id}', [AdminController::class, 'updatePersonnel']);

    // Supprimer un personnel par ID
    Route::delete('personnel/{id}', [AdminController::class, 'deletePersonnel']);

    // Mettre à jour le statut d'un utilisateur
    Route::post('update-status/{id}', [AdminController::class, 'updateStatusUser']);
});
