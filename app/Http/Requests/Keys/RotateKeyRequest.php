<?php

namespace App\Http\Requests\Keys;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class RotateKeyRequest extends FormRequest
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
    #[ArrayShape(["unlocking_key" => "string", "slots" => "string", "slots.*" => "string"])]
    public function rules(): array
    {
        return [
            "unlocking_key" => "required|string|min:8",
            "slots" => "required|array|min:1|max:8",
            "slots.*" => "required|string|min:8",
        ];
    }

    /**
     * @return string[]
     */
    public function validatedSlotArrays(): array
    {
        return $this->validated()["slots"];
    }

    /**
     * @return string
     */
    public function validatedUnlockingKey(): string
    {
        return $this->validated()["unlocking_key"];
    }
}
