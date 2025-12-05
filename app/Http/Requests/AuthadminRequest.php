<?php

namespace App\Http\Requests;

use App\DTOs\ArticleDTO;
use App\DTOs\LoginDTO;
//*//use App\Helpers\ResponseHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AuthadminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'exists:users,email'],
            'password' => ['required']
        ];
    }
    /**
     * Send an error response.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     * @return void
     */
protected function failedValidation(Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            ResponseHelper::Validate(
                $validator->errors()->toArray(), // data
                'Validation error.',             // message
                422                               // code (int)
            )
        );
    }
    public function toDTO(): LoginDTO
    {
        return LoginDTO::fromRequest($this);
    }
}
