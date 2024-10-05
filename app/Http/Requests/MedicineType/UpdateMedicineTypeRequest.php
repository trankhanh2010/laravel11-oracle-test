<?php

namespace App\Http\Requests\MedicineType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateMedicineTypeRequest extends FormRequest
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
        if(!is_numeric($this->medicine_type)){
            throw new HttpResponseException(returnIdError($this->medicine_type));
        }
        return [
            'medicine_type_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:25',
                                                    Rule::unique('App\Models\HIS\MedicineType')->ignore($this->medicine_type),
                                                ],
            'medicine_type_name' =>        'required|string|max:1500',
            'service_id'  =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'parent_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicineType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
                'not_in:'.$this->medicine_type,
            ], 
            'is_leaf' =>                    'nullable|integer|in:0,1', 
            'num_order'=>                   'nullable|integer',
            'concentra' =>                  'nullable|string|max:1000',
            'active_ingr_bhyt_code'  =>     'nullable|string|max:500',
            'active_ingr_bhyt_name'  =>     'nullable|string|max:1000',
            'register_number'  =>           'nullable|string|max:500',
            'packing_type_id_delete'    =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\PackingType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],  
            'manufacturer_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Manufacturer', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],     
            'medicine_use_form_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicineUseForm', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],           
            'medicine_line_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicineLine', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],                
            'medicine_group_id'   =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicineGroup', 'id')
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
            'tdl_gender_id'    =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Gender', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],                
            'national_name' =>                  'nullable|string|max:100',    
            'tutorial'  =>                      'nullable|string|max:2000',
            'imp_price' =>                      'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'imp_vat_ratio'  =>                 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|max:1',    
            'internal_price' =>                 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'alert_max_in_treatment' =>         'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0', 
            'alert_expired_date' =>             'nullable|integer', 
            'alert_min_in_stock' =>             'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0', 
            'alert_max_in_prescription' =>      'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0', 
            'is_stop_imp'  =>                   'nullable|integer|in:0,1', 
            'is_star_mark' =>                   'nullable|integer|in:0,1', 
            'is_allow_odd'  =>                  'nullable|integer|in:0,1',   
            'is_allow_export_odd' =>            'nullable|integer|in:0,1', 
            'is_functional_food'  =>            'nullable|integer|in:0,1', 
            'is_require_hsd' =>                 'nullable|integer|in:0,1', 
            'is_sale_equal_imp_price' =>        'nullable|integer|in:0,1', 
            'is_business'  =>                   'nullable|integer|in:0,1', 
            'is_raw_medicine'  =>               'nullable|integer|in:0,1', 
            'is_auto_expend'  =>                'nullable|integer|in:0,1', 
            'is_vitamin_a' =>                   'nullable|integer|in:0,1', 
            'is_vaccine' =>                     'nullable|integer|in:0,1', 
            'is_tcmr' =>                        'nullable|integer|in:0,1', 
            'is_must_prepare' =>                'nullable|integer|in:0,1', 
            'use_on_day'  =>                    'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0', 
            'description'  =>                   'nullable|string|max:2000',  
            'mema_group_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MemaGroup', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],                  
            'byt_num_order' =>                  'nullable|string|max:50',  
            'tcy_num_order' =>                  'nullable|string|max:20',  
            'medicine_type_proprietary_name' => 'nullable|string|max:200',
            'packing_type_name'  =>             'nullable|string|max:300',
            'rank' =>                           'nullable|integer',
            'medicine_national_code' =>         'nullable|string|max:30',
            'is_kidney' =>                      'nullable|integer|in:0,1', 
            'is_chemical_substance' =>          'nullable|integer|in:0,1', 
            'last_exp_price' =>                 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'last_exp_vat_ratio'  =>            'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'contraindication'=>                'nullable|string|max:4000',
            'last_imp_price'  =>                'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'last_imp_vat_ratio' =>             'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'atc_codes'   =>                    'nullable|string|max:500',
            'last_expired_date'  =>             'nullable|integer|regex:/^\d{14}$/',
            'recording_transaction'  =>         'nullable|string|max:20',
            'is_treatment_day_count' =>         'nullable|integer|in:0,1', 
            'allow_missing_pkg_info' =>         'nullable|integer|in:0,1', 
            'is_block_max_in_prescription' =>   'nullable|integer|in:0,1', 
            'is_oxygen' =>                      'nullable|integer|in:0,1', 
            'is_split_compensation' =>          'nullable|integer|in:0,1', 
            'storage_condition_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\StorageCondition', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],                 
            'contraindication_ids'  =>          'nullable|string|max:500',
            'is_out_hospital'  =>               'nullable|integer|in:0,1', 
            'imp_unit_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ServiceUnit', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],                 
            'imp_unit_convert_ratio'  =>        'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'scientific_name'  =>               'nullable|string|max:500',
            'preprocessing'  =>                 'nullable|string|max:1000',
            'processing' =>                     'nullable|string|max:1000',
            'used_part'  =>                     'nullable|string|max:500',
            'dosage_form'  =>                   'nullable|string|max:2000',
            'distributed_amount' =>             'nullable|string|max:500', 
            'is_not_treatment_day_count'  =>    'nullable|integer|in:0,1',
            'is_anaesthesia'  =>                'nullable|integer|in:0,1',
            'vaccine_type_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\VaccineType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],               
            'quality_standards'  =>             'nullable|string|max:1000', 
            'source_medicine'  =>               'nullable|integer|in:1,2',
            'is_drug_store' =>                  'nullable|integer|in:0,1',
            'locking_reason'  =>                'nullable|string|max:4000', 
            'preprocessing_code' =>             'nullable|string|max:255', 
            'processing_code'  =>               'nullable|string|max:255',  
            'num_order_circulars20'  =>         'nullable|string|max:50',  
            'is_block_max_in_day'  =>           'nullable|integer|in:0,1',
            'alert_max_in_day'  =>              'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/|min:0', 
            'htu_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Htu', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],                 
            'odd_warning_content' =>            'nullable|string|max:2000', 
            'is_original_brand_name'  =>        'nullable|integer|in:0,1',
            'is_generic' =>                     'nullable|integer|in:0,1',
            'is_biologic'  =>                   'nullable|integer|in:0,1',
            'atc_group_codes'  =>               'nullable|string|max:500', 
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'medicine_type_code.required'    => config('keywords')['medicine_type']['medicine_type_code'].config('keywords')['error']['required'],
            'medicine_type_code.string'      => config('keywords')['medicine_type']['medicine_type_code'].config('keywords')['error']['string'],
            'medicine_type_code.max'         => config('keywords')['medicine_type']['medicine_type_code'].config('keywords')['error']['string_max'],
            'medicine_type_code.unique'      => config('keywords')['medicine_type']['medicine_type_code'].config('keywords')['error']['unique'],

            'medicine_type_name.string'      => config('keywords')['medicine_type']['medicine_type_name'].config('keywords')['error']['string'],
            'medicine_type_name.max'         => config('keywords')['medicine_type']['medicine_type_name'].config('keywords')['error']['string_max'],
            'medicine_type_name.unique'      => config('keywords')['medicine_type']['medicine_type_name'].config('keywords')['error']['unique'],

            'service_id.required'    => config('keywords')['medicine_type']['service_id'].config('keywords')['error']['required'],
            'service_id.integer'     => config('keywords')['medicine_type']['service_id'].config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['medicine_type']['service_id'].config('keywords')['error']['exists'],

            'parent_id.integer'     => config('keywords')['medicine_type']['parent_id'].config('keywords')['error']['integer'],
            'parent_id.exists'      => config('keywords')['medicine_type']['parent_id'].config('keywords')['error']['exists'],
            'parent_id.not_in'      => config('keywords')['error']['parent_not_in_id'],

            'is_leaf.integer'     => config('keywords')['medicine_type']['is_leaf'].config('keywords')['error']['integer'],
            'is_leaf.in'          => config('keywords')['medicine_type']['is_leaf'].config('keywords')['error']['in'],

            'num_order.integer'     => config('keywords')['medicine_type']['num_order'].config('keywords')['error']['integer'],

            'concentra.string'      => config('keywords')['medicine_type']['concentra'].config('keywords')['error']['string'],
            'concentra.max'         => config('keywords')['medicine_type']['concentra'].config('keywords')['error']['string_max'],

            'active_ingr_bhyt_code.string'      => config('keywords')['medicine_type']['active_ingr_bhyt_code'].config('keywords')['error']['string'],
            'active_ingr_bhyt_code.max'         => config('keywords')['medicine_type']['active_ingr_bhyt_code'].config('keywords')['error']['string_max'],

            'active_ingr_bhyt_name.string'      => config('keywords')['medicine_type']['active_ingr_bhyt_name'].config('keywords')['error']['string'],
            'active_ingr_bhyt_name.max'         => config('keywords')['medicine_type']['active_ingr_bhyt_name'].config('keywords')['error']['string_max'],

            'register_number.string'      => config('keywords')['medicine_type']['register_number'].config('keywords')['error']['string'],
            'register_number.max'         => config('keywords')['medicine_type']['register_number'].config('keywords')['error']['string_max'],

            'packing_type_id_delete.integer'     => config('keywords')['medicine_type']['packing_type_id_delete'].config('keywords')['error']['integer'],
            'packing_type_id_delete.exists'      => config('keywords')['medicine_type']['packing_type_id_delete'].config('keywords')['error']['exists'],

            'manufacturer_id.integer'     => config('keywords')['medicine_type']['manufacturer_id'].config('keywords')['error']['integer'],
            'manufacturer_id.exists'      => config('keywords')['medicine_type']['manufacturer_id'].config('keywords')['error']['exists'],

            'medicine_use_form_id.integer'     => config('keywords')['medicine_type']['medicine_use_form_id'].config('keywords')['error']['integer'],
            'medicine_use_form_id.exists'      => config('keywords')['medicine_type']['medicine_use_form_id'].config('keywords')['error']['exists'],

            'medicine_line_id.integer'     => config('keywords')['medicine_type']['medicine_line_id'].config('keywords')['error']['integer'],
            'medicine_line_id.exists'      => config('keywords')['medicine_type']['medicine_line_id'].config('keywords')['error']['exists'],

            'medicine_group_id.integer'     => config('keywords')['medicine_type']['medicine_group_id'].config('keywords')['error']['integer'],
            'medicine_group_id.exists'      => config('keywords')['medicine_type']['medicine_group_id'].config('keywords')['error']['exists'],

            'tdl_service_unit_id.required'    => config('keywords')['medicine_type']['tdl_service_unit_id'].config('keywords')['error']['required'],
            'tdl_service_unit_id.integer'     => config('keywords')['medicine_type']['tdl_service_unit_id'].config('keywords')['error']['integer'],
            'tdl_service_unit_id.exists'      => config('keywords')['medicine_type']['tdl_service_unit_id'].config('keywords')['error']['exists'],

            'tdl_gender_id.integer'     => config('keywords')['medicine_type']['tdl_gender_id'].config('keywords')['error']['integer'],
            'tdl_gender_id.exists'      => config('keywords')['medicine_type']['tdl_gender_id'].config('keywords')['error']['exists'],

            'national_name.string'      => config('keywords')['medicine_type']['national_name'].config('keywords')['error']['string'],
            'national_name.max'         => config('keywords')['medicine_type']['national_name'].config('keywords')['error']['string_max'],

            'tutorial.string'      => config('keywords')['medicine_type']['tutorial'].config('keywords')['error']['string'],
            'tutorial.max'         => config('keywords')['medicine_type']['tutorial'].config('keywords')['error']['string_max'],

            'imp_price.numeric'     => config('keywords')['medicine_type']['imp_price'].config('keywords')['error']['numeric'],
            'imp_price.regex'       => config('keywords')['medicine_type']['imp_price'].config('keywords')['error']['regex_19_4'],
            'imp_price.min'         => config('keywords')['medicine_type']['imp_price'].config('keywords')['error']['integer_min'],

            'imp_vat_ratio.numeric'     => config('keywords')['medicine_type']['imp_vat_ratio'].config('keywords')['error']['numeric'],
            'imp_vat_ratio.regex'       => config('keywords')['medicine_type']['imp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'imp_vat_ratio.min'         => config('keywords')['medicine_type']['imp_vat_ratio'].config('keywords')['error']['integer_min'],
            'imp_vat_ratio.max'         => config('keywords')['medicine_type']['imp_vat_ratio'].config('keywords')['error']['integer_max'],

            'internal_price.numeric'     => config('keywords')['medicine_type']['internal_price'].config('keywords')['error']['numeric'],
            'internal_price.regex'       => config('keywords')['medicine_type']['internal_price'].config('keywords')['error']['regex_19_4'],
            'internal_price.min'         => config('keywords')['medicine_type']['internal_price'].config('keywords')['error']['integer_min'],

            'alert_max_in_treatment.numeric'     => config('keywords')['medicine_type']['alert_max_in_treatment'].config('keywords')['error']['numeric'],
            'alert_max_in_treatment.regex'       => config('keywords')['medicine_type']['alert_max_in_treatment'].config('keywords')['error']['regex_19_2'],
            'alert_max_in_treatment.min'         => config('keywords')['medicine_type']['alert_max_in_treatment'].config('keywords')['error']['integer_min'],

            'alert_expired_date.integer'     => config('keywords')['medicine_type']['alert_expired_date'].config('keywords')['error']['integer'],

            'alert_min_in_stock.numeric'     => config('keywords')['medicine_type']['alert_min_in_stock'].config('keywords')['error']['numeric'],
            'alert_min_in_stock.regex'       => config('keywords')['medicine_type']['alert_min_in_stock'].config('keywords')['error']['regex_19_2'],
            'alert_min_in_stock.min'         => config('keywords')['medicine_type']['alert_min_in_stock'].config('keywords')['error']['integer_min'],

            'alert_max_in_prescription.numeric'     => config('keywords')['medicine_type']['alert_max_in_prescription'].config('keywords')['error']['numeric'],
            'alert_max_in_prescription.regex'       => config('keywords')['medicine_type']['alert_max_in_prescription'].config('keywords')['error']['regex_19_2'],
            'alert_max_in_prescription.min'         => config('keywords')['medicine_type']['alert_max_in_prescription'].config('keywords')['error']['integer_min'],

            'is_stop_imp.integer'     => config('keywords')['medicine_type']['is_stop_imp'].config('keywords')['error']['integer'],
            'is_stop_imp.in'          => config('keywords')['medicine_type']['is_stop_imp'].config('keywords')['error']['in'],

            'is_star_mark.integer'     => config('keywords')['medicine_type']['is_star_mark'].config('keywords')['error']['integer'],
            'is_star_mark.in'          => config('keywords')['medicine_type']['is_star_mark'].config('keywords')['error']['in'],

            'is_allow_odd.integer'     => config('keywords')['medicine_type']['is_allow_odd'].config('keywords')['error']['integer'],
            'is_allow_odd.in'          => config('keywords')['medicine_type']['is_allow_odd'].config('keywords')['error']['in'],

            'is_allow_export_odd.integer'     => config('keywords')['medicine_type']['is_allow_export_odd'].config('keywords')['error']['integer'],
            'is_allow_export_odd.in'          => config('keywords')['medicine_type']['is_allow_export_odd'].config('keywords')['error']['in'],

            'is_functional_food.integer'     => config('keywords')['medicine_type']['is_functional_food'].config('keywords')['error']['integer'],
            'is_functional_food.in'          => config('keywords')['medicine_type']['is_functional_food'].config('keywords')['error']['in'],

            'is_sale_equal_imp_price.integer'     => config('keywords')['medicine_type']['is_sale_equal_imp_price'].config('keywords')['error']['integer'],
            'is_sale_equal_imp_price.in'          => config('keywords')['medicine_type']['is_sale_equal_imp_price'].config('keywords')['error']['in'],

            'is_business.integer'     => config('keywords')['medicine_type']['is_business'].config('keywords')['error']['integer'],
            'is_business.in'          => config('keywords')['medicine_type']['is_business'].config('keywords')['error']['in'],

            'is_raw_medicine.integer'     => config('keywords')['medicine_type']['is_raw_medicine'].config('keywords')['error']['integer'],
            'is_raw_medicine.in'          => config('keywords')['medicine_type']['is_raw_medicine'].config('keywords')['error']['in'],

            'is_auto_expend.integer'     => config('keywords')['medicine_type']['is_auto_expend'].config('keywords')['error']['integer'],
            'is_auto_expend.in'          => config('keywords')['medicine_type']['is_auto_expend'].config('keywords')['error']['in'],

            'is_vitamin_a.integer'     => config('keywords')['medicine_type']['is_vitamin_a'].config('keywords')['error']['integer'],
            'is_vitamin_a.in'          => config('keywords')['medicine_type']['is_vitamin_a'].config('keywords')['error']['in'],

            'is_vaccine.integer'     => config('keywords')['medicine_type']['is_vaccine'].config('keywords')['error']['integer'],
            'is_vaccine.in'          => config('keywords')['medicine_type']['is_vaccine'].config('keywords')['error']['in'],

            'is_tcmr.integer'     => config('keywords')['medicine_type']['is_tcmr'].config('keywords')['error']['integer'],
            'is_tcmr.in'          => config('keywords')['medicine_type']['is_tcmr'].config('keywords')['error']['in'],

            'is_must_prepare.integer'     => config('keywords')['medicine_type']['is_must_prepare'].config('keywords')['error']['integer'],
            'is_must_prepare.in'          => config('keywords')['medicine_type']['is_must_prepare'].config('keywords')['error']['in'],
            
            'use_on_day.numeric'     => config('keywords')['medicine_type']['use_on_day'].config('keywords')['error']['numeric'],
            'use_on_day.regex'       => config('keywords')['medicine_type']['use_on_day'].config('keywords')['error']['regex_19_2'],
            'use_on_day.min'         => config('keywords')['medicine_type']['use_on_day'].config('keywords')['error']['integer_min'],

            'description.string'      => config('keywords')['medicine_type']['description'].config('keywords')['error']['string'],
            'description.max'         => config('keywords')['medicine_type']['description'].config('keywords')['error']['string_max'],

            'mema_group_id.integer'     => config('keywords')['medicine_type']['mema_group_id'].config('keywords')['error']['integer'],
            'mema_group_id.exists'      => config('keywords')['medicine_type']['mema_group_id'].config('keywords')['error']['exists'],

            'byt_num_order.string'      => config('keywords')['medicine_type']['byt_num_order'].config('keywords')['error']['string'],
            'byt_num_order.max'         => config('keywords')['medicine_type']['byt_num_order'].config('keywords')['error']['string_max'],

            'tcy_num_order.string'      => config('keywords')['medicine_type']['tcy_num_order'].config('keywords')['error']['string'],
            'tcy_num_order.max'         => config('keywords')['medicine_type']['tcy_num_order'].config('keywords')['error']['string_max'],

            'medicine_type_proprietary_name.string'      => config('keywords')['medicine_type']['medicine_type_proprietary_name'].config('keywords')['error']['string'],
            'medicine_type_proprietary_name.max'         => config('keywords')['medicine_type']['medicine_type_proprietary_name'].config('keywords')['error']['string_max'],
        
            'packing_type_name.string'      => config('keywords')['medicine_type']['packing_type_name'].config('keywords')['error']['string'],
            'packing_type_name.max'         => config('keywords')['medicine_type']['packing_type_name'].config('keywords')['error']['string_max'],

            'rank.integer'     => config('keywords')['medicine_type']['rank'].config('keywords')['error']['integer'],

            'medicine_national_code.string'      => config('keywords')['medicine_type']['medicine_national_code'].config('keywords')['error']['string'],
            'medicine_national_code.max'         => config('keywords')['medicine_type']['medicine_national_code'].config('keywords')['error']['string_max'],
            
            'is_chemical_substance.integer'     => config('keywords')['medicine_type']['is_chemical_substance'].config('keywords')['error']['integer'],
            'is_chemical_substance.in'          => config('keywords')['medicine_type']['is_chemical_substance'].config('keywords')['error']['in'],
            
            'is_kidney.integer'     => config('keywords')['medicine_type']['is_kidney'].config('keywords')['error']['integer'],
            'is_kidney.in'          => config('keywords')['medicine_type']['is_kidney'].config('keywords')['error']['in'],

            'last_exp_price.numeric'     => config('keywords')['medicine_type']['last_exp_price'].config('keywords')['error']['numeric'],
            'last_exp_price.regex'       => config('keywords')['medicine_type']['last_exp_price'].config('keywords')['error']['regex_19_4'],
            'last_exp_price.min'         => config('keywords')['medicine_type']['last_exp_price'].config('keywords')['error']['integer_min'],

            'last_exp_vat_ratio.numeric'     => config('keywords')['medicine_type']['last_exp_vat_ratio'].config('keywords')['error']['numeric'],
            'last_exp_vat_ratio.regex'       => config('keywords')['medicine_type']['last_exp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'last_exp_vat_ratio.min'         => config('keywords')['medicine_type']['last_exp_vat_ratio'].config('keywords')['error']['integer_min'],
            
            'contraindication.string'      => config('keywords')['medicine_type']['contraindication'].config('keywords')['error']['string'],
            'contraindication.max'         => config('keywords')['medicine_type']['contraindication'].config('keywords')['error']['string_max'],

            'last_imp_price.numeric'     => config('keywords')['medicine_type']['last_imp_price'].config('keywords')['error']['numeric'],
            'last_imp_price.regex'       => config('keywords')['medicine_type']['last_imp_price'].config('keywords')['error']['regex_19_4'],
            'last_imp_price.min'         => config('keywords')['medicine_type']['last_imp_price'].config('keywords')['error']['integer_min'],

            'last_imp_vat_ratio.numeric'     => config('keywords')['medicine_type']['last_imp_vat_ratio'].config('keywords')['error']['numeric'],
            'last_imp_vat_ratio.regex'       => config('keywords')['medicine_type']['last_imp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'last_imp_vat_ratio.min'         => config('keywords')['medicine_type']['last_imp_vat_ratio'].config('keywords')['error']['integer_min'],

            'atc_codes.string'      => config('keywords')['medicine_type']['atc_codes'].config('keywords')['error']['string'],
            'atc_codes.max'         => config('keywords')['medicine_type']['atc_codes'].config('keywords')['error']['string_max'],

            'last_expired_date.integer'            => config('keywords')['medicine_type']['last_expired_date'].config('keywords')['error']['integer'],
            'last_expired_date.regex'              => config('keywords')['medicine_type']['last_expired_date'].config('keywords')['error']['regex_ymdhis'],

            'recording_transaction.string'      => config('keywords')['medicine_type']['recording_transaction'].config('keywords')['error']['string'],
            'recording_transaction.max'         => config('keywords')['medicine_type']['recording_transaction'].config('keywords')['error']['string_max'],

            'is_treatment_day_count.integer'     => config('keywords')['medicine_type']['is_treatment_day_count'].config('keywords')['error']['integer'],
            'is_treatment_day_count.in'          => config('keywords')['medicine_type']['is_treatment_day_count'].config('keywords')['error']['in'],

            'allow_missing_pkg_info.integer'     => config('keywords')['medicine_type']['allow_missing_pkg_info'].config('keywords')['error']['integer'],
            'allow_missing_pkg_info.in'          => config('keywords')['medicine_type']['allow_missing_pkg_info'].config('keywords')['error']['in'],

            'is_block_max_in_prescription.integer'     => config('keywords')['medicine_type']['is_block_max_in_prescription'].config('keywords')['error']['integer'],
            'is_block_max_in_prescription.in'          => config('keywords')['medicine_type']['is_block_max_in_prescription'].config('keywords')['error']['in'],

            'is_oxygen.integer'     => config('keywords')['medicine_type']['is_oxygen'].config('keywords')['error']['integer'],
            'is_oxygen.in'          => config('keywords')['medicine_type']['is_oxygen'].config('keywords')['error']['in'],

            'is_split_compensation.integer'     => config('keywords')['medicine_type']['is_split_compensation'].config('keywords')['error']['integer'],
            'is_split_compensation.in'          => config('keywords')['medicine_type']['is_split_compensation'].config('keywords')['error']['in'],

            'storage_condition_id.integer'     => config('keywords')['medicine_type']['storage_condition_id'].config('keywords')['error']['integer'],
            'storage_condition_id.exists'      => config('keywords')['medicine_type']['storage_condition_id'].config('keywords')['error']['exists'],
            
            'contraindication_ids.string'      => config('keywords')['medicine_type']['contraindication_ids'].config('keywords')['error']['string'],
            'contraindication_ids.max'         => config('keywords')['medicine_type']['contraindication_ids'].config('keywords')['error']['string_max'],

            'is_out_hospital.integer'     => config('keywords')['medicine_type']['is_out_hospital'].config('keywords')['error']['integer'],
            'is_out_hospital.in'          => config('keywords')['medicine_type']['is_out_hospital'].config('keywords')['error']['in'],

            'imp_unit_id.integer'     => config('keywords')['medicine_type']['imp_unit_id'].config('keywords')['error']['integer'],
            'imp_unit_id.exists'      => config('keywords')['medicine_type']['imp_unit_id'].config('keywords')['error']['exists'],

            'imp_unit_convert_ratio.numeric'     => config('keywords')['medicine_type']['imp_unit_convert_ratio'].config('keywords')['error']['numeric'],
            'imp_unit_convert_ratio.regex'       => config('keywords')['medicine_type']['imp_unit_convert_ratio'].config('keywords')['error']['regex_19_4'],
            'imp_unit_convert_ratio.min'         => config('keywords')['medicine_type']['imp_unit_convert_ratio'].config('keywords')['error']['integer_min'],
            
            'scientific_name.string'      => config('keywords')['medicine_type']['scientific_name'].config('keywords')['error']['string'],
            'scientific_name.max'         => config('keywords')['medicine_type']['scientific_name'].config('keywords')['error']['string_max'],

            'preprocessing.string'      => config('keywords')['medicine_type']['preprocessing'].config('keywords')['error']['string'],
            'preprocessing.max'         => config('keywords')['medicine_type']['preprocessing'].config('keywords')['error']['string_max'],
            
            'processing.string'      => config('keywords')['medicine_type']['processing'].config('keywords')['error']['string'],
            'processing.max'         => config('keywords')['medicine_type']['processing'].config('keywords')['error']['string_max'],

            'used_part.string'      => config('keywords')['medicine_type']['used_part'].config('keywords')['error']['string'],
            'used_part.max'         => config('keywords')['medicine_type']['used_part'].config('keywords')['error']['string_max'],

            'dosage_form.string'      => config('keywords')['medicine_type']['dosage_form'].config('keywords')['error']['string'],
            'dosage_form.max'         => config('keywords')['medicine_type']['dosage_form'].config('keywords')['error']['string_max'],

            'distributed_amount.string'      => config('keywords')['medicine_type']['distributed_amount'].config('keywords')['error']['string'],
            'distributed_amount.max'         => config('keywords')['medicine_type']['distributed_amount'].config('keywords')['error']['string_max'],

            'is_not_treatment_day_count.integer'     => config('keywords')['medicine_type']['is_not_treatment_day_count'].config('keywords')['error']['integer'],
            'is_not_treatment_day_count.in'          => config('keywords')['medicine_type']['is_not_treatment_day_count'].config('keywords')['error']['in'],

            'is_anaesthesia.integer'     => config('keywords')['medicine_type']['is_anaesthesia'].config('keywords')['error']['integer'],
            'is_anaesthesia.in'          => config('keywords')['medicine_type']['is_anaesthesia'].config('keywords')['error']['in'],

            'vaccine_type_id.integer'     => config('keywords')['medicine_type']['vaccine_type_id'].config('keywords')['error']['integer'],
            'vaccine_type_id.exists'      => config('keywords')['medicine_type']['vaccine_type_id'].config('keywords')['error']['exists'],

            'quality_standards.string'      => config('keywords')['medicine_type']['quality_standards'].config('keywords')['error']['string'],
            'quality_standards.max'         => config('keywords')['medicine_type']['quality_standards'].config('keywords')['error']['string_max'],

            'source_medicine.integer'     => config('keywords')['medicine_type']['source_medicine'].config('keywords')['error']['integer'],
            'source_medicine.in'          => config('keywords')['medicine_type']['source_medicine'].config('keywords')['error']['in'],

            'is_drug_store.integer'     => config('keywords')['medicine_type']['is_drug_store'].config('keywords')['error']['integer'],
            'is_drug_store.in'          => config('keywords')['medicine_type']['is_drug_store'].config('keywords')['error']['in'],

            'locking_reason.string'      => config('keywords')['medicine_type']['locking_reason'].config('keywords')['error']['string'],
            'locking_reason.max'         => config('keywords')['medicine_type']['locking_reason'].config('keywords')['error']['string_max'],

            'preprocessing_code.string'      => config('keywords')['medicine_type']['preprocessing_code'].config('keywords')['error']['string'],
            'preprocessing_code.max'         => config('keywords')['medicine_type']['preprocessing_code'].config('keywords')['error']['string_max'],

            'processing_code.string'      => config('keywords')['medicine_type']['processing_code'].config('keywords')['error']['string'],
            'processing_code.max'         => config('keywords')['medicine_type']['processing_code'].config('keywords')['error']['string_max'],

            'num_order_circulars20.string'      => config('keywords')['medicine_type']['num_order_circulars20'].config('keywords')['error']['string'],
            'num_order_circulars20.max'         => config('keywords')['medicine_type']['num_order_circulars20'].config('keywords')['error']['string_max'],

            'is_block_max_in_day.integer'     => config('keywords')['medicine_type']['is_block_max_in_day'].config('keywords')['error']['integer'],
            'is_block_max_in_day.in'          => config('keywords')['medicine_type']['is_block_max_in_day'].config('keywords')['error']['in'],

            'alert_max_in_day.numeric'     => config('keywords')['medicine_type']['alert_max_in_day'].config('keywords')['error']['numeric'],
            'alert_max_in_day.regex'       => config('keywords')['medicine_type']['alert_max_in_day'].config('keywords')['error']['regex_19_2'],
            'alert_max_in_day.min'         => config('keywords')['medicine_type']['alert_max_in_day'].config('keywords')['error']['integer_min'],

            'htu_id.integer'     => config('keywords')['medicine_type']['htu_id'].config('keywords')['error']['integer'],
            'htu_id.exists'      => config('keywords')['medicine_type']['htu_id'].config('keywords')['error']['exists'],

            'odd_warning_content.string'      => config('keywords')['medicine_type']['odd_warning_content'].config('keywords')['error']['string'],
            'odd_warning_content.max'         => config('keywords')['medicine_type']['odd_warning_content'].config('keywords')['error']['string_max'],

            'is_original_brand_name.integer'     => config('keywords')['medicine_type']['is_original_brand_name'].config('keywords')['error']['integer'],
            'is_original_brand_name.in'          => config('keywords')['medicine_type']['is_original_brand_name'].config('keywords')['error']['in'],

            'is_generic.integer'     => config('keywords')['medicine_type']['is_generic'].config('keywords')['error']['integer'],
            'is_generic.in'          => config('keywords')['medicine_type']['is_generic'].config('keywords')['error']['in'],

            'is_biologic.integer'     => config('keywords')['medicine_type']['is_biologic'].config('keywords')['error']['integer'],
            'is_biologic.in'          => config('keywords')['medicine_type']['is_biologic'].config('keywords')['error']['in'],

            'atc_group_codes.string'      => config('keywords')['medicine_type']['atc_group_codes'].config('keywords')['error']['string'],
            'atc_group_codes.max'         => config('keywords')['medicine_type']['atc_group_codes'].config('keywords')['error']['string_max'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('atc_codes')) {
            $this->merge([
                'atc_codes_list' => explode(',', $this->atc_codes),
            ]);
        }
        if ($this->has('contraindication_ids')) {
            $this->merge([
                'contraindication_ids_list' => explode(',', $this->contraindication_ids),
            ]);
        }
        if ($this->has('preprocessing_code')) {
            $this->merge([
                'preprocessing_code_list' => explode(',', $this->preprocessing_code),
            ]);
        }
        if ($this->has('processing_code')) {
            $this->merge([
                'processing_code_list' => explode(',', $this->processing_code),
            ]);
        }
        if ($this->has('atc_group_codes')) {
            $this->merge([
                'atc_group_codes_list' => explode(',', $this->atc_group_codes),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('atc_codes_list') && ($this->atc_codes_list[0] != null)) {
                foreach ($this->atc_codes_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\Atc::where('atc_code', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('atc_codes', 'Atc với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('contraindication_ids_list') && ($this->contraindication_ids_list[0] != null)) {
                foreach ($this->contraindication_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Contraindication::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('contraindication_ids', 'Chống chỉ định với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('preprocessing_code_list') && ($this->preprocessing_code_list[0] != null)) {
                foreach ($this->preprocessing_code_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\ProcessingMethod::where('PROCESSING_METHOD_CODE', $id)->where('is_active', 1)->where('PROCESSING_METHOD_TYPE', 1)->first()) {
                        $validator->errors()->add('preprocessing_code', 'Phương pháp sơ chế với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('processing_code_list') && ($this->processing_code_list[0] != null)) {
                foreach ($this->processing_code_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\ProcessingMethod::where('PROCESSING_METHOD_CODE', $id)->where('is_active', 1)->where('PROCESSING_METHOD_TYPE', 2)->first()) {
                        $validator->errors()->add('processing_code', 'Phương pháp phục chế với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('atc_group_codes_list') && ($this->atc_group_codes_list[0] != null)) {
                foreach ($this->atc_group_codes_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\AtcGroup::where('Atc_group_code', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('atc_group_codes', 'Nhóm Atc với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
        });
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
