<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JWTAuthController;
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

Route::prefix('/v1')->group(function() {

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
    });

    // routes using jwt token
    Route::middleware(['auth:jwt'])->group(function () {
        Route::get('/verify', fn(Request $request) => $request->user());
    });
});
