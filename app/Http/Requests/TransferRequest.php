<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * @var mixed
     */

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
            'type' => 'required|in:deposit,withdraw,transfer',
            'amount' => 'numeric|min:0',
            'from_account_id' => 'nullable|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
        ];
    }
    // //required_if:type,withdraw,required_if:type,transfer',

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Account type must be one of: withdraw,deposit,transfer.',
            'from_account_id.exists' => 'from_account_id account does not exist.',
            'amount.numeric' => 'Balance must be a valid number.',
            'amount.min' => 'Balance cannot be negative.',
        ];
    }
}
