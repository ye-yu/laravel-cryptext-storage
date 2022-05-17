<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        return response()->noContent();
    }

    function register (RegisterRequest $request) {
        $createAttributes = $request->validateAndParse();
        $user = User::factory()->create($createAttributes);
        Auth::login($user, true);
        return $request->user();
    }

    /**
     * @throws ValidationException
     */
    function login (LoginRequest $request) {
    $loginAttributes = $request->validateAndParse();
    error_log("Login:" . $request . implode(", ", $loginAttributes));

    if (!Auth::attempt($request->only('email', 'password'), true)) {
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    $request->session()->regenerate();

    return $request->user();
}
}
