<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\RegisterController;
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

        Route::get('test',function(Request $request){
            return 'test';
        });
    });

    
    Route::group(['middleware'=>'driver'], function(){

        Route::get('test',function(Request $request){
            return 'test';
        });
    });
});
