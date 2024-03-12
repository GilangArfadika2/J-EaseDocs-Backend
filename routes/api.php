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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::prefix('api')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/isLogin', [AuthController::class, 'isLogin']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::patch('/user/name', [AuthController::class, 'updateName']);
    
    Route::get('user', [AuthController::class, 'getAllUser']);
    Route::get('user-detail/{id}', [AuthController::class, 'getUserById'])->where('id', '[0-9]+');
    Route::delete('/user-delete/{id}', [AuthController::class, 'deleteUser'])->where('id', '[0-9]+');
    Route::get('editPassword',[AuthController::class, 'editPassword']);
    Route::patch('/user/password', [AuthController::class, 'updatePassword']);
    Route::get('editUser/{id}', [AuthController::class, 'updateUserGetter'])->where('id', '[0-9]+');
    Route::patch('updateUser', [AuthController::class, 'updateUser']);

    Route::post('/letter/create', [LetterController::class, 'createLetter']);
Route::patch('letter/update', [LetterController::class, 'updateLetter']);
Route::post('letter', [LetterController::class, 'getAllLetter']);
Route::post('letter/detail', [LetterController::class, 'getLetterById']);

Route::post('/otp/verify-otp', [LetterController::class, 'verifyOTP']);
Route::patch('/letter/update-decision', [LetterController::class, 'updateDecision']);
Route::get('/otp/{id}', [LetterController::class, 'getOtpById']);
Route::get('/letter/bulk', [LetterController::class, 'getLetterByBulkUserId']);


// Additional routes...

