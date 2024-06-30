<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LetterTemplateController;


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

    // log admin 1
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/addNewUser', [AuthController::class, 'registerGetter']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/isLogin', [AuthController::class, 'isLogin']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::patch('/user/name', [AuthController::class, 'updateName']);
    
    Route::get('user', [AuthController::class, 'getAllUser']);
    Route::get('user-detail/{id}', [AuthController::class, 'getUserById'])->where('id', '[0-9]+');
    // log admin 2
    Route::delete('user-delete/{id}', [AuthController::class, 'deleteUser'])->where('id', '[0-9]+');
    Route::get('editPassword',[AuthController::class, 'editPassword']);
    Route::patch('/user/password', [AuthController::class, 'updatePassword']);
    Route::get('editUser/{id}', [AuthController::class, 'updateUserGetter'])->where('id', '[0-9]+');
    // log admin 3
    Route::patch('updateUser', [AuthController::class, 'updateUser']);

        // Route::post('letter/create', [LetterController::class, 'createLetter']);
        Route::post('letter/new', [LetterController::class,'CreateLetter']);
        Route::patch('letter/update', [LetterController::class, 'updateLetter']);
        Route::post('letter', [LetterController::class, 'getAllLetter']);
        Route::post('letter/detail', [LetterController::class, 'getLetterByID']);
        Route::get('letter-template/isian/{id}', [LetterController::class, 'getLetterTemplateFieldByID']);
        // getLetterDataFileByID

        Route::post('/letter/verify-otp', [LetterController::class, 'verifyOTP']);
        Route::patch('/letter/update-decision', [LetterController::class, 'updateDecision']);
        Route::get('/otp/{id}', [LetterController::class, 'getOtpById']);
        Route::get('/letter/bulk', [LetterController::class, 'getLetterByBulkUserId']);
        Route::get('/otp/regenerate/{email}/{id}', [LetterController::class, 'resendOtp']);
        Route::get('/Arsip', [LetterController::class,'getAllArsip']);
        Route::get('/Arsip/{nomorSurat}' , [LetterController::class,'getArsipByID']);
        Route::post('/template-surat/create', [LetterTemplateController::class, 'CreateLetterTemplate']);
        Route::patch('/template-surat/update', [LetterTemplateController::class, 'UpdateLetterTemplate']);
        Route::get('/template-surat', [LetterTemplateController::class, 'index']);
        Route::get('/template-surat/{id}', [LetterTemplateController::class, 'getLetterTemplateById']);
        // Route::post('/template-surat/attachment', [LetterTemplateController::class, 'fetchFile']);
        Route::post('/template-surat/attachment', [LetterController::class, 'generateDocument']);
        Route::get('/letter/barcode/{nomorSurat}', [LetterController::class, 'getLetterBarcodeDetail']);

        //logAdmin
        Route::get('/inbox/countUnread', [LetterController::class,"getUnreadNotificationCount"]);
        Route::get('/logAudit', [LogController::class,'getAllAdmin']);
        Route::get('convert', [LetterTemplateController::class, 'convertWordToPdf']);
        Route::get('/log/{nomorSurat}', [LogController::class, 'getLogSurat']);
        

// Additional routes...

