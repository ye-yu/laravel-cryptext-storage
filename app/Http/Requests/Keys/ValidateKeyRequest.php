<?php

namespace App\Http\Requests\Keys;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class ValidateKeyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(["key" => "string"])]
    public function rules(): array
    {
        return [
            "key" => "required|string|min:8",
        ];
    }

    public function validatedKey()
    {
        return $this->validated()["key"];
    }
}
