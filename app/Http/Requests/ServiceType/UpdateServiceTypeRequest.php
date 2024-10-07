<?php

namespace App\Http\Requests\ServiceType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateServiceTypeRequest extends FormRequest
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
        if(!is_numeric($this->service_type)){
            throw new HttpResponseException(returnIdError($this->service_type));
        }
        return [
            'service_type_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\ServiceType')->ignore($this->service_type),
                                                ],
            'service_type_name' =>        'required|string|max:100',
            'num_order'  =>             'nullable|integer',    
            'exe_service_module_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ExeServiceModule', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'is_auto_split_req' =>      'nullable|integer|in:0,1',
            'is_not_display_assign' =>  'nullable|integer|in:0,1', 
            'is_split_req_by_sample_type'  =>   'nullable|integer|in:0,1', 
            'is_required_sample_type'  =>       'nullable|integer|in:0,1', 
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'service_type_code.required'    => config('keywords')['service_type']['service_type_code'].config('keywords')['error']['required'],
            'service_type_code.string'      => config('keywords')['service_type']['service_type_code'].config('keywords')['error']['string'],
            'service_type_code.max'         => config('keywords')['service_type']['service_type_code'].config('keywords')['error']['string_max'],
            'service_type_code.unique'      => config('keywords')['service_type']['service_type_code'].config('keywords')['error']['unique'],

            'service_type_name.required'    => config('keywords')['service_type']['service_type_name'].config('keywords')['error']['required'],
            'service_type_name.string'      => config('keywords')['service_type']['service_type_name'].config('keywords')['error']['string'],
            'service_type_name.max'         => config('keywords')['service_type']['service_type_name'].config('keywords')['error']['string_max'],
            'service_type_name.unique'      => config('keywords')['service_type']['service_type_name'].config('keywords')['error']['unique'],

            'num_order.integer'      => config('keywords')['service_type']['num_order'].config('keywords')['error']['integer'],

            'exe_service_module_id.integer'     => config('keywords')['service_type']['exe_service_module_id'].config('keywords')['error']['integer'],
            'exe_service_module_id.exists'      => config('keywords')['service_type']['exe_service_module_id'].config('keywords')['error']['exists'],

            'is_auto_split_req.integer'     => config('keywords')['service_type']['is_auto_split_req'].config('keywords')['error']['integer'], 
            'is_auto_split_req.in'          => config('keywords')['service_type']['is_auto_split_req'].config('keywords')['error']['in'], 

            'is_not_display_assign.integer'     => config('keywords')['service_type']['is_not_display_assign'].config('keywords')['error']['integer'], 
            'is_not_display_assign.in'          => config('keywords')['service_type']['is_not_display_assign'].config('keywords')['error']['in'], 

            'is_split_req_by_sample_type.integer'     => config('keywords')['service_type']['is_split_req_by_sample_type'].config('keywords')['error']['integer'], 
            'is_split_req_by_sample_type.in'          => config('keywords')['service_type']['is_split_req_by_sample_type'].config('keywords')['error']['in'],

            'is_required_sample_type.integer'     => config('keywords')['service_type']['is_required_sample_type'].config('keywords')['error']['integer'], 
            'is_required_sample_type.in'          => config('keywords')['service_type']['is_required_sample_type'].config('keywords')['error']['in'],

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
