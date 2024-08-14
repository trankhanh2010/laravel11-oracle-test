<?php

namespace App\Http\Requests\BidType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateBidTypeRequest extends FormRequest
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
            'bid_type_code' =>      'required|string|max:2|unique:App\Models\HIS\BidType,bid_type_code',
            'bid_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'bid_type_code.required'    => config('keywords')['bid_type']['bid_type_code'].config('keywords')['error']['required'],
            'bid_type_code.string'      => config('keywords')['bid_type']['bid_type_code'].config('keywords')['error']['string'],
            'bid_type_code.max'         => config('keywords')['bid_type']['bid_type_code'].config('keywords')['error']['string_max'],
            'bid_type_code.unique'      => config('keywords')['bid_type']['bid_type_code'].config('keywords')['error']['unique'],

            'bid_type_name.required'    => config('keywords')['bid_type']['bid_type_name'].config('keywords')['error']['required'],
            'bid_type_name.string'      => config('keywords')['bid_type']['bid_type_name'].config('keywords')['error']['string'],
            'bid_type_name.max'         => config('keywords')['bid_type']['bid_type_name'].config('keywords')['error']['string_max'],

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
