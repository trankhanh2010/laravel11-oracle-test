<?php

namespace App\Http\Requests\RelationList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateRelationListRequest extends FormRequest
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
        if(!is_numeric($this->relation_list)){
            throw new HttpResponseException(returnIdError($this->relation_list));
        }
        return [
            'relation_code' => [
                'required',
                'string',
                'max:2',
                Rule::unique('App\Models\EMR\Relation')->ignore($this->relation_list),
            ],
            'relation_name' =>      'required|string|max:100',
            'is_active' =>             'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'relation_code.required'    => config('keywords')['relation_list']['relation_code'].config('keywords')['error']['required'],
            'relation_code.string'      => config('keywords')['relation_list']['relation_code'].config('keywords')['error']['string'],
            'relation_code.max'         => config('keywords')['relation_list']['relation_code'].config('keywords')['error']['string_max'],
            'relation_code.unique'      => config('keywords')['relation_list']['relation_code'].config('keywords')['error']['unique'],

            'relation_name.required'    => config('keywords')['relation_list']['relation_name'].config('keywords')['error']['required'],
            'relation_name.string'      => config('keywords')['relation_list']['relation_name'].config('keywords')['error']['string'],
            'relation_name.max'         => config('keywords')['relation_list']['relation_name'].config('keywords')['error']['string_max'],

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
