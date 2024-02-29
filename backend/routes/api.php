<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register',[RegisterController::class,'submit']);
Route::post('/verify',[RegisterController::class,'verify']);
Route::post('/login', [AuthenticatedUserController::class,'submit']);
Route::post('/forgot_password', [AuthenticatedUserController::class,'forgotPassword']);
Route::post('verify_password_change', [AuthenticatedUserController::class,'verifyPasswordChange']);

Route::group(['middleware' => 'auth:sanctum'], function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('change_password', [AuthenticatedUserController::class,'changePassword']);



    Route::group(['middleware'=>'user'], function(){

        Route::post('driver',[DriverController::class,'becomeDriver']);

        Route::prefix('trip')->group(function(){

            Route::post('/',[TripController::class,'book']);
            Route::post('/cancel/{trip}',[TripController::class,'book']);
            Route::get('/show/{trip}',[TripController::class,'show']);


        });
    });

    
    Route::group(['middleware'=>'driver'], function(){

        Route::prefix('trip')->group(function(){

            Route::get('/show/{trip}',[TripController::class,'show']);
            Route::get('/all',[TripController::class,'all']);
            Route::post('/cancel/{trip}',[TripController::class,'book']);
            Route::post('/accept/{trip}',[TripController::class,'accept']);
            Route::post('/start/{trip}',[TripController::class,'start']);
            Route::post('/complete{trip}',[TripController::class,'complete']);
            Route::post('/location{trip}',[TripController::class,'location']);

        });


    });
});
