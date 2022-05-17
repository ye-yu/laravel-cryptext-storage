<?php

namespace App\Http\Requests\Secrets;

use App\Utils\Utils;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class CreateNoteRequest extends FormRequest
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
    #[ArrayShape(['key' => "string", 'name' => "string", 'content' => "string"])]
    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'name' => 'required|string|unique:secrets',
            'content' => 'required|string|min:1'
        ];
    }

    #[ArrayShape(['key' => "string", 'name' => "string", 'content' => "string"])]
    public function validatedBody(): array
    {
        $formData = $this->validated();
        return [
            'key' => $formData['key'],
            'name' => $formData['name'],
            'content' => $formData['content'],
        ];
    }
}
