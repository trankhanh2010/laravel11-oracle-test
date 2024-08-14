<?php

namespace App\Http\Requests\DeathWithin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateDeathWithinRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'death_within_code' =>        [
                                                'required',
                                                'string',
                                                'max:10',
                                                Rule::unique('App\Models\HIS\DeathWithin')->ignore($this->id),
                                            ],
            'death_within_name' =>      'required|string|max:500',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'death_within_code.required'    => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['required'],
            'death_within_code.string'      => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['string'],
            'death_within_code.max'         => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['string_max'],
            'death_within_code.unique'      => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['unique'],

            'death_within_name.required'    => config('keywords')['death_within']['death_within_name'].config('keywords')['error']['required'],
            'death_within_name.string'      => config('keywords')['death_within']['death_within_name'].config('keywords')['error']['string'],
            'death_within_name.max'         => config('keywords')['death_within']['death_within_name'].config('keywords')['error']['string_max'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
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
