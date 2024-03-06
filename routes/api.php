<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\AuthController;
use  App\Http\Controllers\OtpController;
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

// Route::prefix('api')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/user/name', [AuthController::class, 'updateName']);
    
    Route::get('user', [AuthController::class, 'getAllUser']);
    Route::get('user-detail/{id}', [AuthController::class, 'getUserById'])->where('id', '[0-9]+');
    Route::delete('user-delete', [AuthController::class, 'deleteUser']);
    Route::patch('/user/password', [AuthController::class, 'updatePassword']);

    Route::post('/otp', [OtpController::class, 'generateOTP']);
    Route::post('/otp/verify', [OtpController::class, 'verifyOTP']);

    

    
// } );