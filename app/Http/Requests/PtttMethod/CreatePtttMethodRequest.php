<?php

namespace App\Http\Requests\PtttMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePtttMethodRequest extends FormRequest
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
            'pttt_method_code' =>      'required|string|max:6|unique:App\Models\HIS\PtttMethod,pttt_method_code',
            'pttt_method_name' =>      'required|string|max:200',
            'pttt_group_id' =>  [
                                    'nullable',
                                    'integer',
                                    Rule::exists('App\Models\HIS\PtttGroup', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
        ];
    }
    public function messages()
    {
        return [
            'pttt_method_code.required'    => config('keywords')['pttt_method']['pttt_method_code'].config('keywords')['error']['required'],
            'pttt_method_code.string'      => config('keywords')['pttt_method']['pttt_method_code'].config('keywords')['error']['string'],
            'pttt_method_code.max'         => config('keywords')['pttt_method']['pttt_method_code'].config('keywords')['error']['string_max'],
            'pttt_method_code.unique'      => config('keywords')['pttt_method']['pttt_method_code'].config('keywords')['error']['unique'],

            'pttt_method_name.required'    => config('keywords')['pttt_method']['pttt_method_name'].config('keywords')['error']['required'],
            'pttt_method_name.string'      => config('keywords')['pttt_method']['pttt_method_name'].config('keywords')['error']['string'],
            'pttt_method_name.max'         => config('keywords')['pttt_method']['pttt_method_name'].config('keywords')['error']['string_max'],

            'pttt_group_id.required'    => config('keywords')['pttt_method']['pttt_group_id'].config('keywords')['error']['required'],
            'pttt_group_id.integer'     => config('keywords')['pttt_method']['pttt_group_id'].config('keywords')['error']['integer'],
            'pttt_group_id.exists'      => config('keywords')['pttt_method']['pttt_group_id'].config('keywords')['error']['exists'],
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