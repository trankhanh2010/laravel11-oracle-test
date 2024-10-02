<?php

namespace App\Http\Requests\MaterialType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateMaterialTypeRequest extends FormRequest
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
        if(!is_numeric($this->material_type)){
            throw new HttpResponseException(returnIdError($this->material_type));
        }
        return [
            'material_type_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:25',
                                                    Rule::unique('App\Models\HIS\MaterialType')->ignore($this->material_type),
                                                ],
            'material_type_name' =>        'required|string|max:1500',
            'service_id'  =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],     
            'parent_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MaterialType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'is_leaf' =>    'nullable|integer|in:0,1', 
            'num_order' =>  'nullable|integer',       
            'concentra' =>  'nullable|string|max:1000',
            'packing_type_id_delete' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\PackingType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],     
            'manufacturer_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Manufacturer', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],  
            'tdl_service_unit_id'  =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\ServiceUnit', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],          
            'tdl_gender_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Gender', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],  
            'national_name' =>              'nullable|string|max:100',
            'imp_price' =>                  'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',       
            'imp_vat_ratio' =>              'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|max:1',        
            'internal_price' =>             'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'alert_expired_date' =>         'nullable|integer',
            'alert_min_in_stock'  =>        'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0',   
            'alert_max_in_prescription'  => 'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0', 
            'is_chemical_substance'  =>     'nullable|integer|in:0,1', 
            'is_auto_expend'  =>            'nullable|integer|in:0,1',
            'is_stent'  =>                  'nullable|integer|in:0,1',
            'is_in_ktc_fee' =>              'nullable|integer|in:0,1',
            'is_allow_odd'  =>              'nullable|integer|in:0,1',  
            'is_allow_export_odd'  =>       'nullable|integer|in:0,1',  
            'is_stop_imp'  =>               'nullable|integer|in:0,1',
            'is_require_hsd'  =>            'nullable|integer|in:0,1',
            'is_sale_equal_imp_price'  =>   'nullable|integer|in:0,1',
            'is_business'  =>               'nullable|integer|in:0,1',
            'is_raw_material'  =>           'nullable|integer|in:0,1',  
            'is_must_prepare'  =>           'nullable|integer|in:0,1',
            'description' =>                'nullable|string|max:2000', 
            'mema_group_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MemaGroup', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],   
            'packing_type_name'=>           'nullable|string|max:300', 
            'is_reusable'   =>              'nullable|integer|in:0,1',
            'max_reuse_count'  =>           'nullable|integer|min:0',
            'material_group_bhyt' =>        'nullable|string|max:500', 
            'material_type_map_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MaterialTypeMap', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],  
            'last_exp_price' =>             'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',      
            'last_exp_vat_ratio' =>         'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',      
            'last_imp_price'  =>            'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',    
            'last_imp_vat_ratio'  =>        'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',    
            'film_size_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\FilmSize', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],  
            'is_film' =>                    'nullable|integer|in:0,1',
            'last_expired_date'  =>         'nullable|integer|regex:/^\d{14}$/',
            'recording_transaction' =>      'nullable|string|max:20', 
            'register_number'  =>           'nullable|string|max:500', 
            'is_consumable' =>              'nullable|integer|in:0,1',
            'is_out_hospital' =>            'nullable|integer|in:0,1',
            'imp_unit_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ServiceUnit', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],  
            'imp_unit_convert_ratio' =>     'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'is_drug_store'  =>             'nullable|integer|in:0,1',
            'is_not_show_tracking' =>       'nullable|integer|in:0,1',
            'locking_reason' =>             'nullable|string|max:4000', 
            'alert_max_in_day' =>           'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0',
            'model_code'  =>                'nullable|string|max:250', 
            'is_identity_management'  =>    'nullable|integer|in:0,1',
            'is_size_required' =>           'nullable|integer|in:0,1',
            'pricing_max_reuse_count' =>    'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'reuse_fee'  =>                 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'is_active' =>                  'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'material_type_code.required'    => config('keywords')['material_type']['material_type_code'].config('keywords')['error']['required'],
            'material_type_code.string'      => config('keywords')['material_type']['material_type_code'].config('keywords')['error']['string'],
            'material_type_code.max'         => config('keywords')['material_type']['material_type_code'].config('keywords')['error']['string_max'],
            'material_type_code.unique'      => config('keywords')['material_type']['material_type_code'].config('keywords')['error']['unique'],

            'material_type_name.string'      => config('keywords')['material_type']['material_type_name'].config('keywords')['error']['string'],
            'material_type_name.max'         => config('keywords')['material_type']['material_type_name'].config('keywords')['error']['string_max'],
            'material_type_name.unique'      => config('keywords')['material_type']['material_type_name'].config('keywords')['error']['unique'],

            
            'service_id.required'    => config('keywords')['material_type']['service_id'].config('keywords')['error']['required'],
            'service_id.integer'     => config('keywords')['material_type']['service_id'] . config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['material_type']['service_id'] . config('keywords')['error']['exists'],

            'parent_id.integer'     => config('keywords')['material_type']['parent_id'] . config('keywords')['error']['integer'],
            'parent_id.exists'      => config('keywords')['material_type']['parent_id'] . config('keywords')['error']['exists'],

            'is_leaf.integer'     => config('keywords')['material_type']['is_leaf'].config('keywords')['error']['integer'], 
            'is_leaf.in'          => config('keywords')['material_type']['is_leaf'].config('keywords')['error']['in'],

            'num_order.integer'     => config('keywords')['material_type']['num_order'].config('keywords')['error']['integer'], 

            'concentra.string'      => config('keywords')['material_type']['concentra'].config('keywords')['error']['string'],
            'concentra.max'         => config('keywords')['material_type']['concentra'].config('keywords')['error']['string_max'],

            'packing_type_id_delete.integer'     => config('keywords')['material_type']['packing_type_id_delete'] . config('keywords')['error']['integer'],
            'packing_type_id_delete.exists'      => config('keywords')['material_type']['packing_type_id_delete'] . config('keywords')['error']['exists'],

            'manufacturer_id.integer'     => config('keywords')['material_type']['manufacturer_id'] . config('keywords')['error']['integer'],
            'manufacturer_id.exists'      => config('keywords')['material_type']['manufacturer_id'] . config('keywords')['error']['exists'],

            'tdl_service_unit_id.required'    => config('keywords')['material_type']['tdl_service_unit_id'].config('keywords')['error']['required'],
            'tdl_service_unit_id.integer'     => config('keywords')['material_type']['tdl_service_unit_id'] . config('keywords')['error']['integer'],
            'tdl_service_unit_id.exists'      => config('keywords')['material_type']['tdl_service_unit_id'] . config('keywords')['error']['exists'],

            'tdl_gender_id.integer'     => config('keywords')['material_type']['tdl_gender_id'] . config('keywords')['error']['integer'],
            'tdl_gender_id.exists'      => config('keywords')['material_type']['tdl_gender_id'] . config('keywords')['error']['exists'],

            'national_name.string'      => config('keywords')['material_type']['national_name'].config('keywords')['error']['string'],
            'national_name.max'         => config('keywords')['material_type']['national_name'].config('keywords')['error']['string_max'],

            'imp_price.numeric'     => config('keywords')['material_type']['imp_price'].config('keywords')['error']['numeric'],
            'imp_price.regex'       => config('keywords')['material_type']['imp_price'].config('keywords')['error']['regex_19_4'],
            'imp_price.min'         => config('keywords')['material_type']['imp_price'].config('keywords')['error']['integer_min'],

            'imp_vat_ratio.numeric'     => config('keywords')['material_type']['imp_vat_ratio'].config('keywords')['error']['numeric'],
            'imp_vat_ratio.regex'       => config('keywords')['material_type']['imp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'imp_vat_ratio.min'         => config('keywords')['material_type']['imp_vat_ratio'].config('keywords')['error']['integer_min'],
            'imp_vat_ratio.max'         => config('keywords')['material_type']['imp_vat_ratio'].config('keywords')['error']['integer_max'],

            'internal_price.numeric'     => config('keywords')['material_type']['internal_price'].config('keywords')['error']['numeric'],
            'internal_price.regex'       => config('keywords')['material_type']['internal_price'].config('keywords')['error']['regex_19_4'],
            'internal_price.min'         => config('keywords')['material_type']['internal_price'].config('keywords')['error']['integer_min'],

            'alert_expired_date.integer'     => config('keywords')['material_type']['alert_expired_date'] . config('keywords')['error']['integer'],

            'alert_min_in_stock.numeric'     => config('keywords')['material_type']['alert_min_in_stock'].config('keywords')['error']['numeric'],
            'alert_min_in_stock.regex'       => config('keywords')['material_type']['alert_min_in_stock'].config('keywords')['error']['regex_19_2'],
            'alert_min_in_stock.min'         => config('keywords')['material_type']['alert_min_in_stock'].config('keywords')['error']['integer_min'],

            'alert_max_in_prescription.numeric'     => config('keywords')['material_type']['alert_max_in_prescription'].config('keywords')['error']['numeric'],
            'alert_max_in_prescription.regex'       => config('keywords')['material_type']['alert_max_in_prescription'].config('keywords')['error']['regex_19_2'],
            'alert_max_in_prescription.min'         => config('keywords')['material_type']['alert_max_in_prescription'].config('keywords')['error']['integer_min'],

            'is_chemical_substance.integer'     => config('keywords')['material_type']['is_chemical_substance'].config('keywords')['error']['integer'], 
            'is_chemical_substance.in'          => config('keywords')['material_type']['is_chemical_substance'].config('keywords')['error']['in'],

            'is_auto_expend.integer'     => config('keywords')['material_type']['is_auto_expend'].config('keywords')['error']['integer'], 
            'is_auto_expend.in'          => config('keywords')['material_type']['is_auto_expend'].config('keywords')['error']['in'],

            'is_stent.integer'     => config('keywords')['material_type']['is_stent'].config('keywords')['error']['integer'], 
            'is_stent.in'          => config('keywords')['material_type']['is_stent'].config('keywords')['error']['in'],

            'is_in_ktc_fee.integer'     => config('keywords')['material_type']['is_in_ktc_fee'].config('keywords')['error']['integer'], 
            'is_in_ktc_fee.in'          => config('keywords')['material_type']['is_in_ktc_fee'].config('keywords')['error']['in'],

            'is_allow_odd.integer'     => config('keywords')['material_type']['is_allow_odd'].config('keywords')['error']['integer'], 
            'is_allow_odd.in'          => config('keywords')['material_type']['is_allow_odd'].config('keywords')['error']['in'],

            'is_allow_export_odd.integer'     => config('keywords')['material_type']['is_allow_export_odd'].config('keywords')['error']['integer'], 
            'is_allow_export_odd.in'          => config('keywords')['material_type']['is_allow_export_odd'].config('keywords')['error']['in'],

            'is_stop_imp.integer'     => config('keywords')['material_type']['is_stop_imp'].config('keywords')['error']['integer'], 
            'is_stop_imp.in'          => config('keywords')['material_type']['is_stop_imp'].config('keywords')['error']['in'],

            'is_require_hsd.integer'     => config('keywords')['material_type']['is_require_hsd'].config('keywords')['error']['integer'], 
            'is_require_hsd.in'          => config('keywords')['material_type']['is_require_hsd'].config('keywords')['error']['in'],

            'is_sale_equal_imp_price.integer'     => config('keywords')['material_type']['is_sale_equal_imp_price'].config('keywords')['error']['integer'], 
            'is_sale_equal_imp_price.in'          => config('keywords')['material_type']['is_sale_equal_imp_price'].config('keywords')['error']['in'],

            'is_business.integer'     => config('keywords')['material_type']['is_business'].config('keywords')['error']['integer'], 
            'is_business.in'          => config('keywords')['material_type']['is_business'].config('keywords')['error']['in'],

            'is_raw_material.integer'     => config('keywords')['material_type']['is_raw_material'].config('keywords')['error']['integer'], 
            'is_raw_material.in'          => config('keywords')['material_type']['is_raw_material'].config('keywords')['error']['in'],

            'is_must_prepare.integer'     => config('keywords')['material_type']['is_must_prepare'].config('keywords')['error']['integer'], 
            'is_must_prepare.in'          => config('keywords')['material_type']['is_must_prepare'].config('keywords')['error']['in'],

            'description.string'      => config('keywords')['material_type']['description'].config('keywords')['error']['string'],
            'description.max'         => config('keywords')['material_type']['description'].config('keywords')['error']['string_max'],

            'mema_group_id.integer'     => config('keywords')['material_type']['mema_group_id'] . config('keywords')['error']['integer'],
            'mema_group_id.exists'      => config('keywords')['material_type']['mema_group_id'] . config('keywords')['error']['exists'],

            'packing_type_name.string'      => config('keywords')['material_type']['packing_type_name'].config('keywords')['error']['string'],
            'packing_type_name.max'         => config('keywords')['material_type']['packing_type_name'].config('keywords')['error']['string_max'],

            'is_reusable.integer'     => config('keywords')['material_type']['is_reusable'].config('keywords')['error']['integer'], 
            'is_reusable.in'          => config('keywords')['material_type']['is_reusable'].config('keywords')['error']['in'],

            'max_reuse_count.integer'     => config('keywords')['material_type']['max_reuse_count'].config('keywords')['error']['integer'], 
            'max_reuse_count.min'         => config('keywords')['material_type']['max_reuse_count'].config('keywords')['error']['integer_min'],

            'material_group_bhyt.string'      => config('keywords')['material_type']['material_group_bhyt'].config('keywords')['error']['string'],
            'material_group_bhyt.max'         => config('keywords')['material_type']['material_group_bhyt'].config('keywords')['error']['string_max'],

            'material_type_map_id.integer'     => config('keywords')['material_type']['material_type_map_id'] . config('keywords')['error']['integer'],
            'material_type_map_id.exists'      => config('keywords')['material_type']['material_type_map_id'] . config('keywords')['error']['exists'],

            'last_exp_price.numeric'     => config('keywords')['material_type']['last_exp_price'].config('keywords')['error']['numeric'],
            'last_exp_price.regex'       => config('keywords')['material_type']['last_exp_price'].config('keywords')['error']['regex_19_4'],
            'last_exp_price.min'         => config('keywords')['material_type']['last_exp_price'].config('keywords')['error']['integer_min'],

            'last_exp_vat_ratio.numeric'     => config('keywords')['material_type']['last_exp_vat_ratio'].config('keywords')['error']['numeric'],
            'last_exp_vat_ratio.regex'       => config('keywords')['material_type']['last_exp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'last_exp_vat_ratio.min'         => config('keywords')['material_type']['last_exp_vat_ratio'].config('keywords')['error']['integer_min'],

            'last_imp_price.numeric'     => config('keywords')['material_type']['last_imp_price'].config('keywords')['error']['numeric'],
            'last_imp_price.regex'       => config('keywords')['material_type']['last_imp_price'].config('keywords')['error']['regex_19_4'],
            'last_imp_price.min'         => config('keywords')['material_type']['last_imp_price'].config('keywords')['error']['integer_min'],

            'last_imp_vat_ratio.numeric'     => config('keywords')['material_type']['last_imp_vat_ratio'].config('keywords')['error']['numeric'],
            'last_imp_vat_ratio.regex'       => config('keywords')['material_type']['last_imp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'last_imp_vat_ratio.min'         => config('keywords')['material_type']['last_imp_vat_ratio'].config('keywords')['error']['integer_min'],

            'film_size_id.integer'     => config('keywords')['material_type']['film_size_id'] . config('keywords')['error']['integer'],
            'film_size_id.exists'      => config('keywords')['material_type']['film_size_id'] . config('keywords')['error']['exists'],

            'is_film.integer'     => config('keywords')['material_type']['is_film'].config('keywords')['error']['integer'], 
            'is_film.in'          => config('keywords')['material_type']['is_film'].config('keywords')['error']['in'],

            'last_expired_date.integer'            => config('keywords')['material_type']['last_expired_date'].config('keywords')['error']['integer'],
            'last_expired_date.regex'              => config('keywords')['material_type']['last_expired_date'].config('keywords')['error']['regex_ymdhis'],

            'recording_transaction.string'      => config('keywords')['material_type']['recording_transaction'].config('keywords')['error']['string'],
            'recording_transaction.max'         => config('keywords')['material_type']['recording_transaction'].config('keywords')['error']['string_max'],

            'register_number.string'      => config('keywords')['material_type']['register_number'].config('keywords')['error']['string'],
            'register_number.max'         => config('keywords')['material_type']['register_number'].config('keywords')['error']['string_max'],

            'is_consumable.integer'     => config('keywords')['material_type']['is_consumable'].config('keywords')['error']['integer'], 
            'is_consumable.in'          => config('keywords')['material_type']['is_consumable'].config('keywords')['error']['in'],

            'is_out_hospital.integer'     => config('keywords')['material_type']['is_out_hospital'].config('keywords')['error']['integer'], 
            'is_out_hospital.in'          => config('keywords')['material_type']['is_out_hospital'].config('keywords')['error']['in'],

            'imp_unit_id.integer'     => config('keywords')['material_type']['imp_unit_id'] . config('keywords')['error']['integer'],
            'imp_unit_id.exists'      => config('keywords')['material_type']['imp_unit_id'] . config('keywords')['error']['exists'],

            'imp_unit_convert_ratio.numeric'     => config('keywords')['material_type']['imp_unit_convert_ratio'].config('keywords')['error']['numeric'],
            'imp_unit_convert_ratio.regex'       => config('keywords')['material_type']['imp_unit_convert_ratio'].config('keywords')['error']['regex_19_4'],
            'imp_unit_convert_ratio.min'         => config('keywords')['material_type']['imp_unit_convert_ratio'].config('keywords')['error']['integer_min'],

            'is_drug_store.integer'     => config('keywords')['material_type']['is_drug_store'].config('keywords')['error']['integer'], 
            'is_drug_store.in'          => config('keywords')['material_type']['is_drug_store'].config('keywords')['error']['in'],

            'is_not_show_tracking.integer'     => config('keywords')['material_type']['is_not_show_tracking'].config('keywords')['error']['integer'], 
            'is_not_show_tracking.in'          => config('keywords')['material_type']['is_not_show_tracking'].config('keywords')['error']['in'],

            'locking_reason.string'      => config('keywords')['material_type']['locking_reason'].config('keywords')['error']['string'],
            'locking_reason.max'         => config('keywords')['material_type']['locking_reason'].config('keywords')['error']['string_max'],

            'alert_max_in_day.numeric'     => config('keywords')['material_type']['alert_max_in_day'].config('keywords')['error']['numeric'],
            'alert_max_in_day.regex'       => config('keywords')['material_type']['alert_max_in_day'].config('keywords')['error']['regex_19_2'],
            'alert_max_in_day.min'         => config('keywords')['material_type']['alert_max_in_day'].config('keywords')['error']['integer_min'],

            'model_code.string'      => config('keywords')['material_type']['model_code'].config('keywords')['error']['string'],
            'model_code.max'         => config('keywords')['material_type']['model_code'].config('keywords')['error']['string_max'],

            'is_identity_management.integer'     => config('keywords')['material_type']['is_identity_management'].config('keywords')['error']['integer'], 
            'is_identity_management.in'          => config('keywords')['material_type']['is_identity_management'].config('keywords')['error']['in'],

            'is_size_required.integer'     => config('keywords')['material_type']['is_size_required'].config('keywords')['error']['integer'], 
            'is_size_required.in'          => config('keywords')['material_type']['is_size_required'].config('keywords')['error']['in'],

            'reuse_fee.numeric'     => config('keywords')['material_type']['reuse_fee'].config('keywords')['error']['numeric'],
            'reuse_fee.regex'       => config('keywords')['material_type']['reuse_fee'].config('keywords')['error']['regex_19_4'],
            'reuse_fee.min'         => config('keywords')['material_type']['reuse_fee'].config('keywords')['error']['integer_min'],

            'pricing_max_reuse_count.numeric'     => config('keywords')['material_type']['pricing_max_reuse_count'].config('keywords')['error']['numeric'],
            'pricing_max_reuse_count.regex'       => config('keywords')['material_type']['pricing_max_reuse_count'].config('keywords')['error']['regex_19_4'],
            'pricing_max_reuse_count.min'         => config('keywords')['material_type']['pricing_max_reuse_count'].config('keywords')['error']['integer_min'],

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
