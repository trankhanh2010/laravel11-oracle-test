<?php

namespace App\Http\Requests\WorkPlace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateWorkPlaceRequest extends FormRequest
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
            'work_place_code' =>      'required|string|max:20|unique:App\Models\HIS\WorkPlace,work_place_code',
            'work_place_name' =>      'required|string|max:500',
            'address' =>        'nullable|string|max:500',
            'director_name' =>  'nullable|string|max:100',
            'tax_code' =>       'nullable|string|max:20',
            'phone' =>          'nullable|string|max:12',
            'contact_name' =>   'nullable|string|max:100',
            'contact_mobile' => 'nullable|string|max:12',
        ];
    }
    public function messages()
    {
        return [
            'work_place_code.required'    => config('keywords')['work_place']['work_place_code'].config('keywords')['error']['required'],
            'work_place_code.string'      => config('keywords')['work_place']['work_place_code'].config('keywords')['error']['string'],
            'work_place_code.max'         => config('keywords')['work_place']['work_place_code'].config('keywords')['error']['string_max'],
            'work_place_code.unique'      => config('keywords')['work_place']['work_place_code'].config('keywords')['error']['unique'],

            'work_place_name.required'    => config('keywords')['work_place']['work_place_name'].config('keywords')['error']['required'],
            'work_place_name.string'      => config('keywords')['work_place']['work_place_name'].config('keywords')['error']['string'],
            'work_place_name.max'         => config('keywords')['work_place']['work_place_name'].config('keywords')['error']['string_max'],

            'address.string'      => config('keywords')['work_place']['address'].config('keywords')['error']['string'],
            'address.max'         => config('keywords')['work_place']['address'].config('keywords')['error']['string_max'],
            
            'director_name.string'      => config('keywords')['work_place']['director_name'].config('keywords')['error']['string'],
            'director_name.max'         => config('keywords')['work_place']['director_name'].config('keywords')['error']['string_max'],
            
            'tax_code.string'      => config('keywords')['work_place']['tax_code'].config('keywords')['error']['string'],
            'tax_code.max'         => config('keywords')['work_place']['tax_code'].config('keywords')['error']['string_max'],
            
            'phone.string'      => config('keywords')['work_place']['phone'].config('keywords')['error']['string'],
            'phone.max'         => config('keywords')['work_place']['phone'].config('keywords')['error']['string_max'],
            
            'contact_name.string'      => config('keywords')['work_place']['contact_name'].config('keywords')['error']['string'],
            'contact_name.max'         => config('keywords')['work_place']['contact_name'].config('keywords')['error']['string_max'],
            
            'contact_mobile.string'      => config('keywords')['work_place']['contact_mobile'].config('keywords')['error']['string'],
            'contact_mobile.max'         => config('keywords')['work_place']['contact_mobile'].config('keywords')['error']['string_max'],
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
