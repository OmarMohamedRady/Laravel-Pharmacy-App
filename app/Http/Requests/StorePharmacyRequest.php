<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StorePharmacyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email'=>['required','unique:users,email,'.$this->user],
            'national_id'=>['required','unique:pharmacies,national_id,'.$this->pharmacy],
            'password' => ['required','min:6'],
            'avatar_image' => ['mimes:jpeg,png,jpg'],
        ];
    }
}
