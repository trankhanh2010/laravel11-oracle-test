<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateGroupRequest extends FormRequest
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
            'group_code' =>      'required|string|max:10|unique:App\Models\SDA\Group,group_code',
            'group_name' =>      'required|string|max:100',
            'g_code' =>         'required|string|max:10',
            'group_type_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\SDA\GroupType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],
            'parent_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\SDA\Group', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],
            'code_path' =>         'nullable|string|max:2000',
            'id_path' =>         'nullable|string|max:2000',
        ];
    }
    public function messages()
    {
        return [
            'group_code.required'    => config('keywords')['group']['group_code'] . config('keywords')['error']['required'],
            'group_code.string'      => config('keywords')['group']['group_code'] . config('keywords')['error']['string'],
            'group_code.max'         => config('keywords')['group']['group_code'] . config('keywords')['error']['string_max'],
            'group_code.unique'      => config('keywords')['group']['group_code'] . config('keywords')['error']['unique'],

            'group_name.required'    => config('keywords')['group']['group_name'] . config('keywords')['error']['required'],
            'group_name.string'      => config('keywords')['group']['group_name'] . config('keywords')['error']['string'],
            'group_name.max'         => config('keywords')['group']['group_name'] . config('keywords')['error']['string_max'],

            'g_code.required'    => config('keywords')['group']['g_code'] . config('keywords')['error']['required'],
            'g_code.string'      => config('keywords')['group']['g_code'] . config('keywords')['error']['string'],
            'g_code.max'         => config('keywords')['group']['g_code'] . config('keywords')['error']['string_max'],

            'group_type_id.integer'     => config('keywords')['group']['group_type_id'] . config('keywords')['error']['integer'],
            'group_type_id.exists'      => config('keywords')['group']['group_type_id'] . config('keywords')['error']['exists'],

            'parent_id.integer'     => config('keywords')['group']['parent_id'] . config('keywords')['error']['integer'],
            'parent_id.exists'      => config('keywords')['group']['parent_id'] . config('keywords')['error']['exists'],

            'code_path.string'      => config('keywords')['group']['code_path'] . config('keywords')['error']['string'],
            'code_path.max'         => config('keywords')['group']['code_path'] . config('keywords')['error']['string_max'],

            'id_path.string'      => config('keywords')['group']['id_path'] . config('keywords')['error']['string'],
            'id_path.max'         => config('keywords')['group']['id_path'] . config('keywords')['error']['string_max'],
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