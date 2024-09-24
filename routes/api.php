<?php

use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CreneauController;
use App\Http\Controllers\api\PasswordResetController;
use App\Http\Controllers\api\PlanningController;
use App\Http\Controllers\api\RendezVousController;
use App\Http\Controllers\api\ServiceController;
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

Route::post('/registerPersonnel',[AdminController::class,'registerPersonnel']);
Route::post('/login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('/register',[\App\Http\Controllers\Api\AuthController::class,'register']);


Route::post('/plannings', [PlanningController::class, 'store']);
Route::get('/plannings/{id}', [PlanningController::class, 'show']);

Route::post('/creneaux', [CreneauController::class, 'store']);
Route::get('/creneaux/{id}', [CreneauController::class, 'show']);

Route::post('/rendez-vous', [RendezVousController::class, 'store']);
Route::get('/rendez-vous/{id}', [RendezVousController::class, 'show']);
Route::get('/mesRdv/{objet}',[RendezVousController::class,'getMedecinAppointment']);



Route::post('/forget-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/addService',[ServiceController::class,'store']);
Route::get('/list-service',[ServiceController::class,'index']);
Route::post('/updateStatusUser/{objet}',[AdminController::class,'updateStatusUser']);
Route::get('/detMed/{objet}',[PlanningController::class,'getDoctorDetails']);
Route::post('/addPlanning',[PlanningController::class,'store']);
Route::get('/allDoctor',[AuthController::class,'getAllDoctor']);
Route::get('/displayPlanning/{objet}/{date}',[PlanningController::class,'getDisponibilites']);
Route::get('/listRdvMed/{objet}',[RendezVousController::class,'getMedecinAppointments']);
Route::post('/valideRdv/{objet}',[RendezVousController::class,'validaterdv']);
Route::get('/getMedecinByService/{objet}',[ServiceController::class,'getMedecinByService']);
Route::post('/testmail',[RendezVousController::class,'testEmail']);
Route::get('/allPatient',[\App\Http\Controllers\api\SecretaireController::class,'getAllPatient']);
Route::get('/appointmentConf',[RendezVousController::class,'getAppointmentC']);

Route::middleware('auth:api')->group(function ()
{
    Route::get('/apointmentsPatient',[RendezVousController::class,'getPatientAppointments']);
    Route::get('/user-profile',[AuthController::class,'profile']);
    Route::get('/user-role',[AuthController::class,'getUserRole']);
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/list-user',[AdminController::class,'getUser']);

    // Pour le medecin et le secreatire
    Route::post('/loginPersonnel',[AdminController::class,'loginPersonnel']);
    Route::put('/user/{id}/update-status', [AdminController::class, 'updateStatusUser']);


    // les services
//    Route::post('/addService',[AdminController::class,'addService']);
//    Route::get('/services', [AdminController::class, 'getServices']);
    Route::get('/list-med-service',[AuthController::class,'getMedecinMemeService']);
    Route::put('/services/{id}', [AdminController::class, 'updateService']);
    Route::delete('/services/{id}', [AdminController::class, 'destroyService']);

    // Pour le patient
    Route::post('/addRdv',[RendezVousController::class,'store']);

});
