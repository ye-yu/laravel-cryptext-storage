<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JWTAuthController extends Controller
{
    public function generateToken(Request $request): JsonResponse
    {
        $formData = $request->validate([
            'expires_in' => 'int|min:1'
        ]);

        Log::info("formData" . implode(", ", $formData));
        $expires_in = key_exists('expires_in' ,$formData) ? $formData["expires_in"] : 3600;

        auth('jwt')->login($request->user('web'));
        $token = auth('jwt')->refresh();
        return $this->createNewToken($token, $expires_in);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     * @param int $expires_in
     * @return JsonResponse
     */
    protected function createNewToken(string $token, int $expires_in): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expires_in,
            'user' => auth()->user()
        ]);
    }
}
