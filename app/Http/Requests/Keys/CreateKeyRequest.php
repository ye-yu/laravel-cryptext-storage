<?php

namespace App\Http\Requests\Keys;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class CreateKeyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(["slots" => "string", "slots.*" => "string"])]
    public function rules(): array
    {
        return [
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
}
