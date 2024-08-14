<?php

namespace App\Http\Requests\BloodVolume;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateBloodVolumeRequest extends FormRequest
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
            'volume' =>             'required|numeric|min:0|regex:/^\d{1,17}(\.\d{1,2})?$/|unique:App\Models\HIS\BloodVolume,volume',
            'is_donation' =>        'nullable|integer|in:0,1',
            
        ];
    }
    public function messages()
    {
        return [
            'volume.required'    => config('keywords')['blood_volume']['volume'].config('keywords')['error']['required'],
            'volume.numeric'     => config('keywords')['blood_volume']['volume'].config('keywords')['error']['numeric'],
            'volume.min'         => config('keywords')['blood_volume']['volume'].config('keywords')['error']['integer_min'],
            'volume.regex'       => config('keywords')['blood_volume']['volume'].config('keywords')['error']['regex_19_2'],
            'volume.unique'      => config('keywords')['blood_volume']['volume'].config('keywords')['error']['unique'],


            'is_donation.integer'      => config('keywords')['blood_volume']['is_donation'].config('keywords')['error']['integer'],
            'is_donation.in'         => config('keywords')['blood_volume']['is_donation'].config('keywords')['error']['in'],

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
