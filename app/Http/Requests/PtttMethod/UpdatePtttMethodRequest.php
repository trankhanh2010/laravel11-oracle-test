<?php

namespace App\Http\Requests\PtttMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdatePtttMethodRequest extends FormRequest
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
        if(!is_numeric($this->pttt_method)){
            throw new HttpResponseException(returnIdError($this->pttt_method));
        }
        return [
            'pttt_method_code' => [
                                    'required',
                                    'string',
                                    'max:6',
                                    Rule::unique('App\Models\HIS\PtttMethod')->ignore($this->pttt_method),
                                ],
            'pttt_method_name' =>      'required|string|max:200',
            'pttt_group_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\PtttGroup', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'is_active' =>               'required|integer|in:0,1'

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

            'pttt_group_id.nullable'    => config('keywords')['pttt_method']['pttt_group_id'].config('keywords')['error']['required'],
            'pttt_group_id.integer'     => config('keywords')['pttt_method']['pttt_group_id'].config('keywords')['error']['integer'],
            'pttt_group_id.exists'      => config('keywords')['pttt_method']['pttt_group_id'].config('keywords')['error']['exists'],

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
