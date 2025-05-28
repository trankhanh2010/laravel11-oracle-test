<?php

namespace App\Http\Requests\BhytBlacklist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateBhytBlacklistRequest extends FormRequest
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
            'hein_card_number' =>      'required|string|max:15|unique:App\Models\HIS\BhytBlacklist,hein_card_number',            
        ];
    }
    public function messages()
    {
        return [
            'hein_card_number.required'    => config('keywords')['bhyt_blacklist']['hein_card_number'].config('keywords')['error']['required'],
            'hein_card_number.string'      => config('keywords')['bhyt_blacklist']['hein_card_number'].config('keywords')['error']['string'],
            'hein_card_number.max'         => config('keywords')['bhyt_blacklist']['hein_card_number'].config('keywords')['error']['string_max'],
            'hein_card_number.unique'      => config('keywords')['bhyt_blacklist']['hein_card_number'].config('keywords')['error']['unique'],


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
