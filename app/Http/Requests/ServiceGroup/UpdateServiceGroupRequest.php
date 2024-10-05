<?php

namespace App\Http\Requests\ServiceGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateServiceGroupRequest extends FormRequest
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
        if(!is_numeric($this->service_group)){
            throw new HttpResponseException(returnIdError($this->service_group));
        }
        return [
            'service_group_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:6',
                                                    Rule::unique('App\Models\HIS\ServiceGroup')->ignore($this->service_group),
                                                ],
            'service_group_name' =>        'required|string|max:100',
            'is_public'  =>             'nullable|integer|in:0,1',
            'num_order'  =>             'nullable|integer|min:0|max:99',
            'parent_service_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'description' =>  'nullable|string|max:1000',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'service_group_code.required'    => config('keywords')['service_group']['service_group_code'].config('keywords')['error']['required'],
            'service_group_code.string'      => config('keywords')['service_group']['service_group_code'].config('keywords')['error']['string'],
            'service_group_code.max'         => config('keywords')['service_group']['service_group_code'].config('keywords')['error']['string_max'],
            'service_group_code.unique'      => config('keywords')['service_group']['service_group_code'].config('keywords')['error']['unique'],

            'service_group_name.required'    => config('keywords')['service_group']['service_group_name'].config('keywords')['error']['required'],
            'service_group_name.string'      => config('keywords')['service_group']['service_group_name'].config('keywords')['error']['string'],
            'service_group_name.max'         => config('keywords')['service_group']['service_group_name'].config('keywords')['error']['string_max'],
            'service_group_name.unique'      => config('keywords')['service_group']['service_group_name'].config('keywords')['error']['unique'],

            
            'is_public.integer'         => config('keywords')['service_group']['is_public'].config('keywords')['error']['integer'],
            'is_public.in'              => config('keywords')['service_group']['is_public'].config('keywords')['error']['in'],

            'num_order.integer'         => config('keywords')['service_group']['num_order'].config('keywords')['error']['integer'],
            'num_order.min'              => config('keywords')['service_group']['num_order'].config('keywords')['error']['integer_min'],
            'num_order.max'              => config('keywords')['service_group']['num_order'].config('keywords')['error']['integer_max'],

            'parent_service_id.integer'     => config('keywords')['service_group']['parent_service_id'].config('keywords')['error']['integer'],
            'parent_service_id.exists'      => config('keywords')['service_group']['parent_service_id'].config('keywords')['error']['exists'],

            'description.string'      => config('keywords')['service_group']['description'].config('keywords')['error']['string'],
            'description.max'         => config('keywords')['service_group']['description'].config('keywords')['error']['string_max'],

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
