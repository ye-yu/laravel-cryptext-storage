<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\KeysController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SecretController;
use App\Utils\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/v1')->group(function () {

    // public API
    Route::get('/csrf', fn() => csrf_token());
    Route::post('/csrf', fn(Request $request) => response()->json([
        'status' => 'ok'
    ]));
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // routes using cookie session
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', fn(Request $request) => $request->user());
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/jwt', [JWTAuthController::class, 'generateToken']);
        Route::get('/notifications/fetch/{pageSize}', [NotificationController::class, 'paginateNotifications']);
        Route::get('/notifications/read/all', [NotificationController::class, 'readAll']);
        Route::get('/notifications/read/{pageSize}', [NotificationController::class, 'markSomeAsRead']);
    });

    // routes using jwt token
    Route::middleware(['auth:jwt'])->group(function () {
        Route::get('/verify', fn(Request $request) => Utils::user($request));
        Route::get('/notes', fn(Request $request) => Utils::user($request)->getAllNotes());
        Route::put('/notes', [SecretController::class, 'createNewNote']);
        Route::post('/notes/{name}', [SecretController::class, 'readNote']);
        Route::get('/keys', fn(Request $request) => Utils::user($request)->getKeySlotsInfo());
        Route::put('/keys', [KeysController::class, 'createKey']);
        Route::post('/keys/rotate', [KeysController::class, 'rotateKey']);
    });
});
