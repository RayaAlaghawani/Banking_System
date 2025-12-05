<?php

namespace App\Http\Requests;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // مهم جداً حتى يسمح بتمرير الطلب
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:6',

            'government_agencie_id' => 'required|exists:government_agencies,id',

            // رقم الجوال: يبدأ بـ 09 وطوله 10 أرقام
            'phone' => [
                'required',
                'regex:/^09\d{8}$/',
                'unique:users,phone',
            ],

            // الرقم الوطني: أرقام فقط
            'national_id' => [
                'required',
                'digits_between:1,50',
                'regex:/^[0-9]+$/',
                'unique:users,national_id',
            ],

            'permissions'   => 'array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ];
    }
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
}
