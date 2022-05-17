<?php

namespace App\Http\Requests;

use App\Utils\Utils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;

class LoginRequest extends FormRequest {
    protected ?string $email;
    protected ?string $name;
    protected ?string $password;


    #[ArrayShape(['email' => "string", 'name' => "string", 'password' => "string"])] public function validateAndParse(): array
    {
        $val = $this->validate($this->rules());

        $email = Utils::nonNullString($val["email"]);
        $password = Utils::nonNullString($val["password"]);

        return [
            'email' => $email,
            'password' => Hash::make($password),
        ];
    }

    #[ArrayShape(['email' => "string[]", 'password' => "string[]"])] static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
