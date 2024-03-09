<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\LetterController;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::patch('/user/name', [AuthController::class, 'updateName']);
Route::get('user', [AuthController::class, 'getAllUser']);
Route::get('user-detail', [AuthController::class, 'getUserById']);
Route::delete('user-delete', [AuthController::class, 'deleteUser']);
Route::patch('/user/password', [AuthController::class, 'updatePassword']);

Route::post('/otp', [OtpController::class, 'generateOTP']);

Route::post('/letter/create', [LetterController::class, 'createLetter']);
Route::patch('letter/update', [LetterController::class, 'updateLetter']);
Route::post('letter', [LetterController::class, 'getAllLetter']);
Route::post('letter/detail', [LetterController::class, 'getLetterById']);

Route::post('/letter/verify-otp', [LetterController::class, 'verifyOTP']);
Route::patch('/letter/update-decision', [LetterController::class, 'updateDecision']);



// Additional routes...

