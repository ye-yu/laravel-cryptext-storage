<?php

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/v1')->group(function() {
    Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/register', function (RegisterRequest $request) {
        $createAttributes = $request->validateAndParse();
        error_log("Register:" . $request . implode(", ", $createAttributes));
        $user = User::factory()->create($createAttributes);
        Auth::login($user, true);
        return $request->user();
    });

    Route::post('/csrf', function () {
        return csrf_token();
    });

    Route::post('/login', function (LoginRequest $request) {
        $loginAttributes = $request->validateAndParse();
        error_log("Login:" . $request . implode(", ", $loginAttributes));

        if (!Auth::attempt($request->only('email', 'password'), true)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return $request->user();
    });

    Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        return response()->noContent();
    });

});
