<?php

namespace App\Http\Requests\TestIndex;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateTestIndexRequest extends FormRequest
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
            'test_index_code' =>      'required|string|max:20|unique:App\Models\HIS\TestIndex,test_index_code',
            'test_index_name' =>      'required|string|max:300',         
            'test_service_type_id'  =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],    
            'test_index_unit_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\TestIndexUnit', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],      
            'num_order' =>              'nullable|integer',
            'default_value'  =>         'nullable|string|max:100', 
            'bhyt_code' =>              'nullable|string|max:20', 
            'bhyt_name'  =>             'nullable|string|max:300',   
            'is_not_show_service'  =>   'nullable|integer|in:0,1',
            'is_important' =>           'nullable|integer|in:0,1',
            'test_index_group_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\TestIndexGroup', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],          
            'is_to_calculate_egfr' =>   'nullable|integer|in:0,1',
            'normation_amount' =>       'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'material_type_id'   =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MaterialType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],          
            'is_blood_abo'  =>          'nullable|integer|in:0,1',
            'is_blood_rh' =>            'nullable|integer|in:0,1',
            'is_hbsag'  =>              'nullable|integer|in:0,1',
            'is_hcv'  =>                'nullable|integer|in:0,1',
            'is_hiv'  =>                'nullable|integer|in:0,1',
            'is_test_harmony_blood' =>  'nullable|integer|in:0,1',
            'convert_ratio_mlct' =>     'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'result_blood_a'  =>        'nullable|string|max:500', 
            'result_blood_b'  =>        'nullable|string|max:500', 
            'result_blood_ab'  =>       'nullable|string|max:500', 
            'result_blood_o' =>         'nullable|string|max:500', 
            'result_blood_rh_plus' =>   'nullable|string|max:500', 
            'result_blood_rh_minus' =>  'nullable|string|max:500', 
        ];
    }
    public function messages()
    {
        return [
            'test_index_code.required'    => config('keywords')['test_index']['test_index_code'].config('keywords')['error']['required'],
            'test_index_code.string'      => config('keywords')['test_index']['test_index_code'].config('keywords')['error']['string'],
            'test_index_code.max'         => config('keywords')['test_index']['test_index_code'].config('keywords')['error']['string_max'],
            'test_index_code.unique'      => config('keywords')['test_index']['test_index_code'].config('keywords')['error']['unique'],

            'test_index_name.required'    => config('keywords')['test_index']['test_index_name'].config('keywords')['error']['required'],
            'test_index_name.string'      => config('keywords')['test_index']['test_index_name'].config('keywords')['error']['string'],
            'test_index_name.max'         => config('keywords')['test_index']['test_index_name'].config('keywords')['error']['string_max'],
            'test_index_name.unique'      => config('keywords')['test_index']['test_index_name'].config('keywords')['error']['unique'],

            'test_service_type_id.required'    => config('keywords')['test_index']['test_service_type_id'].config('keywords')['error']['required'],
            'test_service_type_id.integer'     => config('keywords')['test_index']['test_service_type_id'].config('keywords')['error']['integer'],
            'test_service_type_id.exists'      => config('keywords')['test_index']['test_service_type_id'].config('keywords')['error']['exists'],

            'test_index_unit_id.integer'     => config('keywords')['test_index']['test_index_unit_id'].config('keywords')['error']['integer'],
            'test_index_unit_id.exists'      => config('keywords')['test_index']['test_index_unit_id'].config('keywords')['error']['exists'],

            'num_order.integer'     => config('keywords')['test_index']['num_order'].config('keywords')['error']['integer'],

            'default_value.string'      => config('keywords')['test_index']['default_value'].config('keywords')['error']['string'],
            'default_value.max'         => config('keywords')['test_index']['default_value'].config('keywords')['error']['string_max'],

            'bhyt_code.string'      => config('keywords')['test_index']['bhyt_code'].config('keywords')['error']['string'],
            'bhyt_code.max'         => config('keywords')['test_index']['bhyt_code'].config('keywords')['error']['string_max'],

            'bhyt_name.string'      => config('keywords')['test_index']['bhyt_name'].config('keywords')['error']['string'],
            'bhyt_name.max'         => config('keywords')['test_index']['bhyt_name'].config('keywords')['error']['string_max'],

            'is_not_show_service.integer'      => config('keywords')['test_index']['is_not_show_service'].config('keywords')['error']['integer'],
            'is_not_show_service.in'           => config('keywords')['test_index']['is_not_show_service'].config('keywords')['error']['in'],

            'is_important.integer'      => config('keywords')['test_index']['is_important'].config('keywords')['error']['integer'],
            'is_important.in'           => config('keywords')['test_index']['is_important'].config('keywords')['error']['in'],

            'test_index_group_id.integer'     => config('keywords')['test_index']['test_index_group_id'].config('keywords')['error']['integer'],
            'test_index_group_id.exists'      => config('keywords')['test_index']['test_index_group_id'].config('keywords')['error']['exists'],
            
            'is_to_calculate_egfr.integer'      => config('keywords')['test_index']['is_to_calculate_egfr'].config('keywords')['error']['integer'],
            'is_to_calculate_egfr.in'           => config('keywords')['test_index']['is_to_calculate_egfr'].config('keywords')['error']['in'],

            'normation_amount.numeric'     => config('keywords')['test_index']['normation_amount'].config('keywords')['error']['numeric'],
            'normation_amount.regex'       => config('keywords')['test_index']['normation_amount'].config('keywords')['error']['regex_19_4'],
            'normation_amount.min'         => config('keywords')['test_index']['normation_amount'].config('keywords')['error']['integer_min'],

            'material_type_id.integer'     => config('keywords')['test_index']['material_type_id'].config('keywords')['error']['integer'],
            'material_type_id.exists'      => config('keywords')['test_index']['material_type_id'].config('keywords')['error']['exists'],

            'is_blood_abo.integer'      => config('keywords')['test_index']['is_blood_abo'].config('keywords')['error']['integer'],
            'is_blood_abo.in'           => config('keywords')['test_index']['is_blood_abo'].config('keywords')['error']['in'],

            'is_blood_rh.integer'      => config('keywords')['test_index']['is_blood_rh'].config('keywords')['error']['integer'],
            'is_blood_rh.in'           => config('keywords')['test_index']['is_blood_rh'].config('keywords')['error']['in'],

            'is_hbsag.integer'      => config('keywords')['test_index']['is_hbsag'].config('keywords')['error']['integer'],
            'is_hbsag.in'           => config('keywords')['test_index']['is_hbsag'].config('keywords')['error']['in'],

            'is_hcv.integer'      => config('keywords')['test_index']['is_hcv'].config('keywords')['error']['integer'],
            'is_hcv.in'           => config('keywords')['test_index']['is_hcv'].config('keywords')['error']['in'],

            'is_hiv.integer'      => config('keywords')['test_index']['is_hiv'].config('keywords')['error']['integer'],
            'is_hiv.in'           => config('keywords')['test_index']['is_hiv'].config('keywords')['error']['in'],

            'is_test_harmony_blood.integer'      => config('keywords')['test_index']['is_test_harmony_blood'].config('keywords')['error']['integer'],
            'is_test_harmony_blood.in'           => config('keywords')['test_index']['is_test_harmony_blood'].config('keywords')['error']['in'],

            'convert_ratio_mlct.numeric'     => config('keywords')['test_index']['convert_ratio_mlct'].config('keywords')['error']['numeric'],
            'convert_ratio_mlct.regex'       => config('keywords')['test_index']['convert_ratio_mlct'].config('keywords')['error']['regex_19_4'],
            'convert_ratio_mlct.min'         => config('keywords')['test_index']['convert_ratio_mlct'].config('keywords')['error']['integer_min'],

            'result_blood_a.string'      => config('keywords')['test_index']['result_blood_a'].config('keywords')['error']['string'],
            'result_blood_a.max'         => config('keywords')['test_index']['result_blood_a'].config('keywords')['error']['string_max'],

            'result_blood_b.string'      => config('keywords')['test_index']['result_blood_b'].config('keywords')['error']['string'],
            'result_blood_b.max'         => config('keywords')['test_index']['result_blood_b'].config('keywords')['error']['string_max'],

            'result_blood_ab.string'      => config('keywords')['test_index']['result_blood_ab'].config('keywords')['error']['string'],
            'result_blood_ab.max'         => config('keywords')['test_index']['result_blood_ab'].config('keywords')['error']['string_max'],

            'result_blood_o.string'      => config('keywords')['test_index']['result_blood_o'].config('keywords')['error']['string'],
            'result_blood_o.max'         => config('keywords')['test_index']['result_blood_o'].config('keywords')['error']['string_max'],

            'result_blood_rh_plus.string'      => config('keywords')['test_index']['result_blood_rh_plus'].config('keywords')['error']['string'],
            'result_blood_rh_plus.max'         => config('keywords')['test_index']['result_blood_rh_plus'].config('keywords')['error']['string_max'],

            'result_blood_rh_minus.string'      => config('keywords')['test_index']['result_blood_rh_minus'].config('keywords')['error']['string'],
            'result_blood_rh_minus.max'         => config('keywords')['test_index']['result_blood_rh_minus'].config('keywords')['error']['string_max'],
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
