<?php

namespace App\Http\Requests\UnlimitReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateUnlimitReasonRequest extends FormRequest
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
    public function rules()
    {
        return [
            'unlimit_reason' =>      'required|string|max:500|unique:App\Models\HIS\UnlimitReason,unlimit_reason',
        ];
    }
    public function messages()
    {
        return [
            'unlimit_reason.required'    => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['required'],
            'unlimit_reason.string'      => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['string'],
            'unlimit_reason.max'         => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['string_max'],
            'unlimit_reason.unique'      => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['unique'],

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Dữ liệu không hợp lệ!',
            'data'      => $validator->errors()
        ], 422));
    }
}
