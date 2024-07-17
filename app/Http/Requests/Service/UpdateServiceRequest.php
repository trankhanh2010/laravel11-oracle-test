<?php

namespace App\Http\Requests\Service;

use App\Models\HIS\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdateServiceRequest extends FormRequest
{
    protected $package_price_check = ['AN'];
    protected $package_price_check_string;
    protected $package_price_check_id;

    protected $speciality_code_check = ['XN', 'KH', 'CN'];
    protected $speciality_code_check_string;
    protected $speciality_code_check_id;

    protected $pttt_method_id_check = ['XN', 'GB', 'PT', 'HA', 'TT', 'CN', 'NS', 'SA', 'PH', 'CL'];
    protected $pttt_method_id_check_string;
    protected $pttt_method_id_check_id;

    protected $pttt_group_id_check = ['XN', 'GB', 'PT', 'HA', 'TT', 'CN', 'NS', 'SA', 'PH', 'CL'];
    protected $pttt_group_id_check_string;
    protected $pttt_group_id_check_id;

    protected $hein_limit_price_old_check = ['XN','GB','KH','PT','HA','TT','CN','NS','SA','PH','GI','CL'];
    protected $hein_limit_price_old_check_string;
    protected $hein_limit_price_old_check_id;

    protected $icd_cm_id_check = ['XN', 'GB', 'PT', 'HA', 'TT', 'CN', 'NS', 'SA', 'PH', 'CL'];
    protected $icd_cm_id_check_string;
    protected $icd_cm_id_check_id;

    protected $hein_limit_price_check = ['XN', 'GB', 'KH', 'PT', 'HA', 'TT', 'CN', 'NS', 'SA', 'PH', 'GI', 'CL'];
    protected $hein_limit_price_check_string;
    protected $hein_limit_price_check_id;

    protected $ration_symbol_check = ['AN'];
    protected $ration_symbol_check_string;
    protected $ration_symbol_check_id;

    protected $ration_group_id_check = ['AN'];
    protected $ration_group_id_check_string;
    protected $ration_group_id_check_id;

    protected $pacs_type_code_check = ['XN', 'GB', 'KH', 'PT', 'HA', 'TT', 'CN', 'NS', 'SA', 'PH', 'GI', 'CL'];
    protected $pacs_type_code_check_string;
    protected $pacs_type_code_check_id;

    protected $diim_type_id_check = ['HA'];
    protected $diim_type_id_check_string;
    protected $diim_type_id_check_id;

    protected $fuex_type_id_check = ['CN'];
    protected $fuex_type_id_check_string;
    protected $fuex_type_id_check_id;

    protected $test_type_id_check = ['XN'];
    protected $test_type_id_check_string;
    protected $test_type_id_check_id;

    protected $max_expend_check = ['PT'];
    protected $max_expend_check_string;
    protected $max_expend_check_id;

    protected $number_of_film_check = ['HA'];
    protected $number_of_film_check_string;
    protected $number_of_film_check_id;

    protected $film_size_id_check = ['HA'];
    protected $film_size_id_check_string;
    protected $film_size_id_check_id;

    protected $allow_send_pacs_check = ['GB', 'PT', 'HA', 'TT', 'CN', 'NS', 'SA', 'PH', 'CL'];
    protected $allow_send_pacs_check_string;
    protected $allow_send_pacs_check_id;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }

        $this->speciality_code_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->speciality_code_check)->pluck('id')->implode(',');
        $this->speciality_code_check_string = implode(", ", $this->speciality_code_check);

        $this->package_price_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->package_price_check)->pluck('id')->implode(',');
        $this->package_price_check_string = implode(", ", $this->package_price_check);

        $this->pttt_method_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->pttt_method_id_check)->pluck('id')->implode(',');
        $this->pttt_method_id_check_string = implode(", ", $this->pttt_method_id_check);

        $this->pttt_group_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->pttt_group_id_check)->pluck('id')->implode(',');
        $this->pttt_group_id_check_string = implode(", ", $this->pttt_group_id_check);

        $this->hein_limit_price_old_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->hein_limit_price_old_check)->pluck('id')->implode(',');
        $this->hein_limit_price_old_check_string = implode(", ", $this->hein_limit_price_old_check);

        $this->icd_cm_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->icd_cm_id_check)->pluck('id')->implode(',');
        $this->icd_cm_id_check_string = implode(", ", $this->icd_cm_id_check);

        $this->hein_limit_price_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->hein_limit_price_check)->pluck('id')->implode(',');
        $this->hein_limit_price_check_string = implode(", ", $this->hein_limit_price_check);

        $this->ration_symbol_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->ration_symbol_check)->pluck('id')->implode(',');
        $this->ration_symbol_check_string = implode(", ", $this->ration_symbol_check);

        $this->ration_group_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->ration_group_id_check)->pluck('id')->implode(',');
        $this->ration_group_id_check_string = implode(", ", $this->ration_group_id_check);

        $this->pacs_type_code_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->pacs_type_code_check)->pluck('id')->implode(',');
        $this->pacs_type_code_check_string = implode(", ", $this->pacs_type_code_check);

        $this->diim_type_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->diim_type_id_check)->pluck('id')->implode(',');
        $this->diim_type_id_check_string = implode(", ", $this->diim_type_id_check);
 
        $this->fuex_type_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->fuex_type_id_check)->pluck('id')->implode(',');
        $this->fuex_type_id_check_string = implode(", ", $this->fuex_type_id_check);

        $this->test_type_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->test_type_id_check)->pluck('id')->implode(',');
        $this->test_type_id_check_string = implode(", ", $this->test_type_id_check);

        $this->max_expend_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->max_expend_check)->pluck('id')->implode(',');
        $this->max_expend_check_string = implode(", ", $this->max_expend_check);

        $this->number_of_film_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->number_of_film_check)->pluck('id')->implode(',');
        $this->number_of_film_check_string = implode(", ", $this->number_of_film_check);

        $this->film_size_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->film_size_id_check)->pluck('id')->implode(',');
        $this->film_size_id_check_string = implode(", ", $this->film_size_id_check);

        $this->allow_send_pacs_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->allow_send_pacs_check)->pluck('id')->implode(',');
        $this->allow_send_pacs_check_string = implode(", ", $this->allow_send_pacs_check);
        
        // Nếu id không phải số thì là số 0
        $service_type_id = is_numeric($this->service_type_id) ? (int) $this->service_type_id : 0;

        return [
           'service_type_id' =>                [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\ServiceType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'service_code' =>                   [
                                                    'required',
                                                    'string',
                                                    'max:25',
                                                    Rule::unique('App\Models\HIS\Service')->ignore($this->id),
                                                ],
            'service_name' =>                   'required|string|max:1500',
            'service_unit_id' =>                [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\ServiceUnit', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'speciality_code' =>                [
                                                    'nullable',
                                                    'string',
                                                    'max:3',
                                                    Rule::exists('App\Models\HIS\Speciality', 'speciality_code')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->speciality_code_check_id
                                                ], 
            'hein_service_type_id' =>           [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\HeinServiceType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],  


            'hein_service_bhyt_code' =>         'nullable|string|max:100',
            'hein_service_bhyt_name' =>         'nullable|string|max:1500',
            'hein_order' =>                     'nullable|string|max:20',
            'parent_id' =>                      [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Service', 'id')
                                                    ->where(function ($query) use ($service_type_id) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                                                        ->where(DB::connection('oracle_his')->raw("service_type_id"), $service_type_id);
                                                    }),
                                                    'not_in:'.$this->id,
                                                ], 
            'package_id' =>                     [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Package', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'package_price' =>                  [
                                                    'nullable',
                                                    'numeric',
                                                    'regex:/^\d{1,15}(\.\d{1,4})?$/',
                                                    'prohibited_unless:service_type_id,'.$this->package_price_check_id

                                                ],

            'bill_option' =>                    'nullable|integer|in:0,1,2',
            'bill_patient_type_id' =>           [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\PatientType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'pttt_method_id' =>                 [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\PtttMethod', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->pttt_method_id_check_id
                                                ], 
            'is_not_change_bill_paty' =>        'nullable|integer|in:0,1',
            'applied_patient_classify_ids' =>   'prohibited_if:bill_patient_type_id,null|nullable|string|max:500',
            'applied_patient_type_ids' =>       'prohibited_if:bill_patient_type_id,null|nullable|string|max:100',
            

            'testing_technique' =>              'nullable|string|max:500',
            'default_patient_type_id' =>        [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\PatientType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'pttt_group_id' =>                  [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\PtttGroup', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->pttt_group_id_check_id
                                                ], 
            'hein_limit_price_old' =>           [
                                                    'nullable',
                                                    'numeric',
                                                    'min:0',
                                                    'regex:/^\d{1,15}(\.\d{1,4})?$/',
                                                    'prohibited_unless:service_type_id,'.$this->hein_limit_price_old_check_id
                                                ],
           'icd_cm_id' =>                      [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\IcdCm', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->icd_cm_id_check_id
                                                ],
            'hein_limit_price_in_time' =>       [
                                                    'nullable',
                                                    'integer',
                                                    'regex:/^\d{14}$/',
                                                ],   

            'hein_limit_price' =>               [
                                                    'nullable',
                                                    'numeric',
                                                    'min:0',
                                                    'regex:/^\d{1,15}(\.\d{1,4})?$/',
                                                    'prohibited_unless:service_type_id,'.$this->hein_limit_price_check_id
                                                ],
            'cogs' =>                           [
                                                    'nullable',
                                                    'numeric',
                                                    'min:0',
                                                    'regex:/^\d{1,15}(\.\d{1,4})?$/',
                                                ],
            'ration_symbol' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:10',
                                                    'prohibited_unless:service_type_id,'.$this->ration_symbol_check_id
                                                ],
            'ration_group_id' =>                [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\RationGroup', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->ration_group_id_check_id
                                                ],
            'num_order' =>                      'nullable|integer',
            'pacs_type_code' =>                 [
                                                    'nullable',
                                                    'string',
                                                    'max:20',
                                                    'prohibited_unless:service_type_id,'.$this->pacs_type_code_check_id
                                                ],



            'diim_type_id' =>                   [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\DiimType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->diim_type_id_check_id
                                                ],
            'fuex_type_id' =>                   [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\FuexType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->fuex_type_id_check_id
                                                ],
            'test_type_id' =>                   [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\TestType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->test_type_id_check_id
                                                ],
            'sample_type_code' =>               'nullable|string|max:100',
            'max_expend' =>                     [
                                                    'nullable',
                                                    'numeric',
                                                    'min:0',
                                                    'regex:/^\d{1,15}(\.\d{1,4})?$/',
                                                    'prohibited_unless:service_type_id,'.$this->max_expend_check_id
                                                ],
            'number_of_film' =>                 [
                                                    'nullable',
                                                    'integer',
                                                    'min:0',
                                                    'prohibited_unless:service_type_id,'.$this->number_of_film_check_id
                                                ],


            'film_size_id' =>                   [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\FilmSize', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->film_size_id_check_id
                                                ],
            'min_process_time' =>               'nullable|integer|min:0',
            'min_proc_time_except_paty_ids' =>  'nullable|string|max:200',
            'estimate_duration' =>              [
                                                    'nullable',
                                                    'numeric',
                                                    'min:0',
                                                    'regex:/^\d{1,17}(\.\d{1,2})?$/',
                                                ],
            'max_process_time' =>               'nullable|integer|min:0',
            'max_proc_time_except_paty_ids' =>  'nullable|string|max:200',
            'age_from' =>                       'nullable|integer|min:0',

            'age_to' =>                         'nullable|integer|min:0',
            'max_total_process_time' =>         'nullable|integer|min:0',
            'total_time_except_paty_ids' =>     'nullable|string|max:230',
            'gender_id' =>                      [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Gender', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'min_duration' =>                   'nullable|integer|min:0',
            'max_amount' =>                     'nullable|integer|min:0',

            'body_part_ids' =>                  'nullable|string|max:200',
            'capacity' =>                       'nullable|integer|min:0',
            'warning_sampling_time' =>          'nullable|integer|min:0',
            'exe_service_module_id' =>          [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\ExeServiceModule', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'suim_index_id' =>                  [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\SuimIndex', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'is_kidney' =>                      'nullable|integer|in:0,1',

            'is_antibiotic_resistance' =>       'nullable|integer|in:0,1',
            'is_disallowance_no_execute' =>     'nullable|integer|in:0,1',
            'is_multi_request' =>               'nullable|integer|in:0,1',
            'is_split_service_req' =>           'nullable|integer|in:0,1',
            'is_out_parent_fee' =>              'nullable|integer|in:0,1',
            'is_allow_expend' =>                'nullable|integer|in:0,1',

            'is_auto_expend' =>                 'nullable|integer|in:0,1',
            'is_out_of_drg' =>                  'nullable|integer|in:0,1',
            'is_out_of_management' =>           'nullable|integer|in:0,1',
            'is_other_source_paid' =>           'nullable|integer|in:0,1',
            'is_enable_assign_price' =>         'nullable|integer|in:0,1',
            'is_not_show_tracking' =>           'nullable|integer|in:0,1',

            'must_be_consulted' =>              'nullable|integer|in:0,1',
            'is_block_department_tran' =>       'nullable|integer|in:0,1',
            'allow_simultaneity' =>             'nullable|integer|in:0,1',
            'is_not_required_complete' =>       'nullable|integer|in:0,1',
            'do_not_use_bhyt' =>                'nullable|integer|in:0,1',
            'allow_send_pacs' =>                [
                                                    'nullable',
                                                    'integer',
                                                    'in:0,1',
                                                    'prohibited_unless:service_type_id,'.$this->allow_send_pacs_check_id
                                                ],

             'other_pay_source_id' =>           [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\OtherPaySource', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'attach_assign_print_type_code' =>  'nullable|string|max:100',
            'description' =>                    'nullable|string|max:2000',
            'notice' =>                         'nullable|string|max:4000',
            'tax_rate_type' =>                  'nullable|integer|in:1,2,3,4,5,6',
            'process_code' =>                   'nullable|string|max:50',
            'is_active' =>                      'required|integer|in:0,1'


        ];
    }
    public function messages()
    {
        return [
            'service_type_id.required'      => config('keywords')['service']['service_type_id'].config('keywords')['error']['required'],
            'service_type_id.integer'       => config('keywords')['service']['service_type_id'].config('keywords')['error']['integer'],
            'service_type_id.exists'        => config('keywords')['service']['service_type_id'].config('keywords')['error']['exists'],

            'service_code.required'    => config('keywords')['service']['service_code'].config('keywords')['error']['required'],
            'service_code.string'      => config('keywords')['service']['service_code'].config('keywords')['error']['string'],
            'service_code.max'         => config('keywords')['service']['service_code'].config('keywords')['error']['string_max'],
            'service_code.unique'      => config('keywords')['service']['service_code'].config('keywords')['error']['unique'],

            'service_name.required'    => config('keywords')['service']['service_name'].config('keywords')['error']['required'],
            'service_name.string'      => config('keywords')['service']['service_name'].config('keywords')['error']['string'],
            'service_name.max'         => config('keywords')['service']['service_name'].config('keywords')['error']['string_max'],

            'service_unit_id.required'      => config('keywords')['service']['service_unit_id'].config('keywords')['error']['required'],
            'service_unit_id.integer'       => config('keywords')['service']['service_unit_id'].config('keywords')['error']['integer'],
            'service_unit_id.exists'        => config('keywords')['service']['service_unit_id'].config('keywords')['error']['exists'],

            'speciality_code.string'                => config('keywords')['service']['speciality_code'].config('keywords')['error']['string'],
            'speciality_code.max'                   => config('keywords')['service']['speciality_code'].config('keywords')['error']['string_max'],
            'speciality_code.exists'                => config('keywords')['service']['speciality_code'].config('keywords')['error']['exists'],
            'speciality_code.prohibited_unless'     => config('keywords')['service']['speciality_code'].config('keywords')['error']['prohibited_unless_service_type'].$this->speciality_code_check_string,

            'hein_service_type_id.integer'       => config('keywords')['service']['hein_service_type_id'].config('keywords')['error']['integer'],
            'hein_service_type_id.exists'        => config('keywords')['service']['hein_service_type_id'].config('keywords')['error']['exists'],


            'hein_service_bhyt_code.string'      => config('keywords')['service']['hein_service_bhyt_code'].config('keywords')['error']['string'],
            'hein_service_bhyt_code.max'         => config('keywords')['service']['hein_service_bhyt_code'].config('keywords')['error']['string_max'],

            'hein_service_bhyt_name.string'      => config('keywords')['service']['hein_service_bhyt_name'].config('keywords')['error']['string'],
            'hein_service_bhyt_name.max'         => config('keywords')['service']['hein_service_bhyt_name'].config('keywords')['error']['string_max'],

            'hein_order.string'      => config('keywords')['service']['hein_order'].config('keywords')['error']['string'],
            'hein_order.max'         => config('keywords')['service']['hein_order'].config('keywords')['error']['string_max'],

            'parent_id.integer'       => config('keywords')['service']['parent_id'].config('keywords')['error']['integer'],
            'parent_id.exists'        => config('keywords')['service']['parent_id'].config('keywords')['error']['exists'].config('keywords')['error']['not_in_service_id'],
            'parent_id.not_in'        => config('keywords')['error']['parent_not_in_id'],

            'package_id.integer'       => config('keywords')['service']['package_id'].config('keywords')['error']['integer'],
            'package_id.exists'        => config('keywords')['service']['package_id'].config('keywords')['error']['exists'],

            'package_price.numeric'             => config('keywords')['service']['package_price'].config('keywords')['error']['numeric'],
            'package_price.regex'               => config('keywords')['service']['package_price'].config('keywords')['error']['regex_19_4'],
            'package_price.prohibited_unless'   => config('keywords')['service']['package_price'].config('keywords')['error']['prohibited_unless_service_type'].$this->package_price_check_string,


            'bill_option.integer'   => config('keywords')['service']['bill_option'].config('keywords')['error']['integer'],
            'bill_option.in'        => config('keywords')['service']['bill_option'].config('keywords')['error']['in'],

            'bill_patient_type_id.integer'       => config('keywords')['service']['bill_patient_type_id'].config('keywords')['error']['integer'],
            'bill_patient_type_id.exists'        => config('keywords')['service']['bill_patient_type_id'].config('keywords')['error']['exists'],

            'pttt_method_id.integer'            => config('keywords')['service']['pttt_method_id'].config('keywords')['error']['integer'],
            'pttt_method_id.exists'             => config('keywords')['service']['pttt_method_id'].config('keywords')['error']['exists'],
            'pttt_method_id.prohibited_unless'  => config('keywords')['service']['pttt_method_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->pttt_method_id_check_string,

            'is_not_change_bill_paty.integer'   => config('keywords')['service']['is_not_change_bill_paty'].config('keywords')['error']['integer'],
            'is_not_change_bill_paty.in'        => config('keywords')['service']['is_not_change_bill_paty'].config('keywords')['error']['in'],

            'applied_patient_classify_ids.prohibited_if'    => config('keywords')['service']['applied_patient_classify_ids'].' chỉ được nhập khi '.config('keywords')['service']['bill_patient_type_id'].' được chọn!',
            'applied_patient_classify_ids.string'           => config('keywords')['service']['applied_patient_classify_ids'].config('keywords')['error']['string'],
            'applied_patient_classify_ids.max'              => config('keywords')['service']['applied_patient_classify_ids'].config('keywords')['error']['string_max'],

            'applied_patient_type_ids.prohibited_if'    => config('keywords')['service']['applied_patient_type_ids'].' chỉ được nhập khi '.config('keywords')['service']['bill_patient_type_id'].' được chọn!',
            'applied_patient_type_ids.string'           => config('keywords')['service']['applied_patient_type_ids'].config('keywords')['error']['string'],
            'applied_patient_type_ids.max'              => config('keywords')['service']['applied_patient_type_ids'].config('keywords')['error']['string_max'],


            'testing_technique.string'           => config('keywords')['service']['testing_technique'].config('keywords')['error']['string'],
            'testing_technique.max'              => config('keywords')['service']['testing_technique'].config('keywords')['error']['string_max'],

            'default_patient_type_id.integer'       => config('keywords')['service']['default_patient_type_id'].config('keywords')['error']['integer'],
            'default_patient_type_id.exists'        => config('keywords')['service']['default_patient_type_id'].config('keywords')['error']['exists'],

            'pttt_group_id.integer'             => config('keywords')['service']['pttt_group_id'].config('keywords')['error']['integer'],
            'pttt_group_id.exists'              => config('keywords')['service']['pttt_group_id'].config('keywords')['error']['exists'],
            'pttt_group_id.prohibited_unless'   => config('keywords')['service']['pttt_group_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->pttt_group_id_check_string,

            'hein_limit_price_old.numeric'              => config('keywords')['service']['hein_limit_price_old'].config('keywords')['error']['numeric'],
            'hein_limit_price_old.min'                  => config('keywords')['service']['hein_limit_price_old'].config('keywords')['error']['integer_min'],
            'hein_limit_price_old.regex'                => config('keywords')['service']['hein_limit_price_old'].config('keywords')['error']['regex_19_4'],
            'hein_limit_price_old.prohibited_unless'    => config('keywords')['service']['hein_limit_price_old'].config('keywords')['error']['prohibited_unless_service_type'].$this->hein_limit_price_old_check_string,

            'icd_cm_id.integer'              => config('keywords')['service']['icd_cm_id'].config('keywords')['error']['integer'],
            'icd_cm_id.exists'               => config('keywords')['service']['icd_cm_id'].config('keywords')['error']['exists'],
            'icd_cm_id.prohibited_unless'    => config('keywords')['service']['icd_cm_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->icd_cm_id_check_string,

            'hein_limit_price_in_time.integer'      => config('keywords')['service']['hein_limit_price_in_time'].config('keywords')['error']['integer'],
            'hein_limit_price_in_time.regex'        => config('keywords')['service']['hein_limit_price_in_time'].config('keywords')['error']['regex_ymdhis'],


            'hein_limit_price.numeric'              => config('keywords')['service']['hein_limit_price'].config('keywords')['error']['numeric'],
            'hein_limit_price.min'                  => config('keywords')['service']['hein_limit_price'].config('keywords')['error']['integer_min'],
            'hein_limit_price.regex'                => config('keywords')['service']['hein_limit_price'].config('keywords')['error']['regex_19_4'],
            'hein_limit_price.prohibited_unless'    => config('keywords')['service']['hein_limit_price'].config('keywords')['error']['prohibited_unless_service_type'].$this->hein_limit_price_check_string,

            'cogs.numeric'              => config('keywords')['service']['cogs'].config('keywords')['error']['numeric'],
            'cogs.min'                  => config('keywords')['service']['cogs'].config('keywords')['error']['integer_min'],
            'cogs.regex'                => config('keywords')['service']['cogs'].config('keywords')['error']['regex_19_4'],

            'ration_symbol.string'              => config('keywords')['service']['ration_symbol'].config('keywords')['error']['string'],
            'ration_symbol.max'                 => config('keywords')['service']['ration_symbol'].config('keywords')['error']['string_max'],
            'ration_symbol.prohibited_unless'   => config('keywords')['service']['ration_symbol'].config('keywords')['error']['prohibited_unless_service_type'].$this->ration_symbol_check_string,

            'ration_group_id.integer'              => config('keywords')['service']['ration_group_id'].config('keywords')['error']['integer'],
            'ration_group_id.exists'               => config('keywords')['service']['ration_group_id'].config('keywords')['error']['exists'],
            'ration_group_id.prohibited_unless'    => config('keywords')['service']['ration_group_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->ration_group_id_check_string,
        
            'num_order.integer'     => config('keywords')['service']['num_order'].config('keywords')['error']['integer'],

            'pacs_type_code.string'              => config('keywords')['service']['pacs_type_code'].config('keywords')['error']['string'],
            'pacs_type_code.max'                 => config('keywords')['service']['pacs_type_code'].config('keywords')['error']['string_max'],
            'pacs_type_code.prohibited_unless'   => config('keywords')['service']['pacs_type_code'].config('keywords')['error']['prohibited_unless_service_type'].$this->pacs_type_code_check_string,


            'diim_type_id.integer'              => config('keywords')['service']['diim_type_id'].config('keywords')['error']['integer'],
            'diim_type_id.exists'               => config('keywords')['service']['diim_type_id'].config('keywords')['error']['exists'],
            'diim_type_id.prohibited_unless'    => config('keywords')['service']['diim_type_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->diim_type_id_check_string,

            'fuex_type_id.integer'              => config('keywords')['service']['fuex_type_id'].config('keywords')['error']['integer'],
            'fuex_type_id.exists'               => config('keywords')['service']['fuex_type_id'].config('keywords')['error']['exists'],
            'fuex_type_id.prohibited_unless'    => config('keywords')['service']['fuex_type_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->fuex_type_id_check_string,

            'test_type_id.integer'              => config('keywords')['service']['test_type_id'].config('keywords')['error']['integer'],
            'test_type_id.exists'               => config('keywords')['service']['test_type_id'].config('keywords')['error']['exists'],
            'test_type_id.prohibited_unless'    => config('keywords')['service']['test_type_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->test_type_id_check_string,
        
            'sample_type_code.string'              => config('keywords')['service']['sample_type_code'].config('keywords')['error']['string'],
            'sample_type_code.max'                 => config('keywords')['service']['sample_type_code'].config('keywords')['error']['string_max'],

            'max_expend.numeric'              => config('keywords')['service']['max_expend'].config('keywords')['error']['numeric'],
            'max_expend.min'                  => config('keywords')['service']['max_expend'].config('keywords')['error']['integer_min'],
            'max_expend.regex'                => config('keywords')['service']['max_expend'].config('keywords')['error']['regex_19_4'],
            'max_expend.prohibited_unless'    => config('keywords')['service']['max_expend'].config('keywords')['error']['prohibited_unless_service_type'].$this->max_expend_check_string,
        
            'number_of_film.integer'              => config('keywords')['service']['number_of_film'].config('keywords')['error']['integer'],
            'number_of_film.min'                  => config('keywords')['service']['number_of_film'].config('keywords')['error']['integer_min'],
            'number_of_film.prohibited_unless'    => config('keywords')['service']['number_of_film'].config('keywords')['error']['prohibited_unless_service_type'].$this->number_of_film_check_string,
       

            'film_size_id.integer'              => config('keywords')['service']['film_size_id'].config('keywords')['error']['integer'],
            'film_size_id.exists'               => config('keywords')['service']['film_size_id'].config('keywords')['error']['exists'],
            'film_size_id.prohibited_unless'    => config('keywords')['service']['film_size_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->film_size_id_check_string,
        
            'min_process_time.integer'              => config('keywords')['service']['min_process_time'].config('keywords')['error']['integer'],
            'min_process_time.min'                  => config('keywords')['service']['min_process_time'].config('keywords')['error']['integer_min'],

            'min_proc_time_except_paty_ids.string'              => config('keywords')['service']['min_proc_time_except_paty_ids'].config('keywords')['error']['string'],
            'min_proc_time_except_paty_ids.max'                 => config('keywords')['service']['min_proc_time_except_paty_ids'].config('keywords')['error']['string_max'],

            'estimate_duration.numeric'              => config('keywords')['service']['estimate_duration'].config('keywords')['error']['numeric'],
            'estimate_duration.min'                  => config('keywords')['service']['estimate_duration'].config('keywords')['error']['integer_min'],
            'estimate_duration.regex'                => config('keywords')['service']['estimate_duration'].config('keywords')['error']['regex_19_2'],

            'max_process_time.integer'              => config('keywords')['service']['max_process_time'].config('keywords')['error']['integer'],
            'max_process_time.min'                  => config('keywords')['service']['max_process_time'].config('keywords')['error']['integer_min'],

            'max_proc_time_except_paty_ids.string'              => config('keywords')['service']['max_proc_time_except_paty_ids'].config('keywords')['error']['string'],
            'max_proc_time_except_paty_ids.max'                 => config('keywords')['service']['max_proc_time_except_paty_ids'].config('keywords')['error']['string_max'],

            'age_from.integer'              => config('keywords')['service']['age_from'].config('keywords')['error']['integer'],
            'age_from.min'                  => config('keywords')['service']['age_from'].config('keywords')['error']['integer_min'],
            

            'age_to.integer'              => config('keywords')['service']['age_to'].config('keywords')['error']['integer'],
            'age_to.min'                  => config('keywords')['service']['age_to'].config('keywords')['error']['integer_min'],

            'max_total_process_time.integer'              => config('keywords')['service']['max_total_process_time'].config('keywords')['error']['integer'],
            'max_total_process_time.min'                  => config('keywords')['service']['max_total_process_time'].config('keywords')['error']['integer_min'],

            'total_time_except_paty_ids.string'              => config('keywords')['service']['total_time_except_paty_ids'].config('keywords')['error']['string'],
            'total_time_except_paty_ids.max'                 => config('keywords')['service']['total_time_except_paty_ids'].config('keywords')['error']['string_max'],

            'gender_id.integer'              => config('keywords')['service']['gender_id'].config('keywords')['error']['integer'],
            'gender_id.exists'               => config('keywords')['service']['gender_id'].config('keywords')['error']['exists'],

            'min_duration.integer'              => config('keywords')['service']['min_duration'].config('keywords')['error']['integer'],
            'min_duration.min'                  => config('keywords')['service']['min_duration'].config('keywords')['error']['integer_min'],

            'max_amount.integer'              => config('keywords')['service']['max_amount'].config('keywords')['error']['integer'],
            'max_amount.min'                  => config('keywords')['service']['max_amount'].config('keywords')['error']['integer_min'],


            'body_part_ids.string'              => config('keywords')['service']['body_part_ids'].config('keywords')['error']['string'],
            'body_part_ids.max'                 => config('keywords')['service']['body_part_ids'].config('keywords')['error']['string_max'],   
            
            'capacity.integer'              => config('keywords')['service']['capacity'].config('keywords')['error']['integer'],
            'capacity.min'                  => config('keywords')['service']['capacity'].config('keywords')['error']['integer_min'],

            'warning_sampling_time.integer'              => config('keywords')['service']['warning_sampling_time'].config('keywords')['error']['integer'],
            'warning_sampling_time.min'                  => config('keywords')['service']['warning_sampling_time'].config('keywords')['error']['integer_min'],

            'exe_service_module_id.integer'              => config('keywords')['service']['exe_service_module_id'].config('keywords')['error']['integer'],
            'exe_service_module_id.exists'               => config('keywords')['service']['exe_service_module_id'].config('keywords')['error']['exists'],

            'suim_index_id.integer'              => config('keywords')['service']['suim_index_id'].config('keywords')['error']['integer'],
            'suim_index_id.exists'               => config('keywords')['service']['suim_index_id'].config('keywords')['error']['exists'],

            'is_kidney.integer'             => config('keywords')['service']['is_kidney'].config('keywords')['error']['integer'],
            'is_kidney.in'                  => config('keywords')['service']['is_kidney'].config('keywords')['error']['in'],


            'is_antibiotic_resistance.integer'             => config('keywords')['service']['is_antibiotic_resistance'].config('keywords')['error']['integer'],
            'is_antibiotic_resistance.in'                  => config('keywords')['service']['is_antibiotic_resistance'].config('keywords')['error']['in'],

            'is_disallowance_no_execute.integer'             => config('keywords')['service']['is_disallowance_no_execute'].config('keywords')['error']['integer'],
            'is_disallowance_no_execute.in'                  => config('keywords')['service']['is_disallowance_no_execute'].config('keywords')['error']['in'],

            'is_multi_request.integer'             => config('keywords')['service']['is_multi_request'].config('keywords')['error']['integer'],
            'is_multi_request.in'                  => config('keywords')['service']['is_multi_request'].config('keywords')['error']['in'],

            'is_split_service_req.integer'             => config('keywords')['service']['is_split_service_req'].config('keywords')['error']['integer'],
            'is_split_service_req.in'                  => config('keywords')['service']['is_split_service_req'].config('keywords')['error']['in'],

            'is_out_parent_fee.integer'             => config('keywords')['service']['is_out_parent_fee'].config('keywords')['error']['integer'],
            'is_out_parent_fee.in'                  => config('keywords')['service']['is_out_parent_fee'].config('keywords')['error']['in'],

            'is_allow_expend.integer'             => config('keywords')['service']['is_allow_expend'].config('keywords')['error']['integer'],
            'is_allow_expend.in'                  => config('keywords')['service']['is_allow_expend'].config('keywords')['error']['in'],


            'is_auto_expend.integer'             => config('keywords')['service']['is_auto_expend'].config('keywords')['error']['integer'],
            'is_auto_expend.in'                  => config('keywords')['service']['is_auto_expend'].config('keywords')['error']['in'],

            'is_out_of_drg.integer'             => config('keywords')['service']['is_out_of_drg'].config('keywords')['error']['integer'],
            'is_out_of_drg.in'                  => config('keywords')['service']['is_out_of_drg'].config('keywords')['error']['in'],

            'is_out_of_management.integer'             => config('keywords')['service']['is_out_of_management'].config('keywords')['error']['integer'],
            'is_out_of_management.in'                  => config('keywords')['service']['is_out_of_management'].config('keywords')['error']['in'],

            'is_other_source_paid.integer'             => config('keywords')['service']['is_other_source_paid'].config('keywords')['error']['integer'],
            'is_other_source_paid.in'                  => config('keywords')['service']['is_other_source_paid'].config('keywords')['error']['in'],

            'is_enable_assign_price.integer'             => config('keywords')['service']['is_enable_assign_price'].config('keywords')['error']['integer'],
            'is_enable_assign_price.in'                  => config('keywords')['service']['is_enable_assign_price'].config('keywords')['error']['in'],

            'is_not_show_tracking.integer'             => config('keywords')['service']['is_not_show_tracking'].config('keywords')['error']['integer'],
            'is_not_show_tracking.in'                  => config('keywords')['service']['is_not_show_tracking'].config('keywords')['error']['in'],


            'must_be_consulted.integer'             => config('keywords')['service']['must_be_consulted'].config('keywords')['error']['integer'],
            'must_be_consulted.in'                  => config('keywords')['service']['must_be_consulted'].config('keywords')['error']['in'],

            'is_block_department_tran.integer'             => config('keywords')['service']['is_block_department_tran'].config('keywords')['error']['integer'],
            'is_block_department_tran.in'                  => config('keywords')['service']['is_block_department_tran'].config('keywords')['error']['in'],

            'allow_simultaneity.integer'             => config('keywords')['service']['allow_simultaneity'].config('keywords')['error']['integer'],
            'allow_simultaneity.in'                  => config('keywords')['service']['allow_simultaneity'].config('keywords')['error']['in'],

            'is_not_required_complete.integer'             => config('keywords')['service']['is_not_required_complete'].config('keywords')['error']['integer'],
            'is_not_required_complete.in'                  => config('keywords')['service']['is_not_required_complete'].config('keywords')['error']['in'],

            'do_not_use_bhyt.integer'             => config('keywords')['service']['do_not_use_bhyt'].config('keywords')['error']['integer'],
            'do_not_use_bhyt.in'                  => config('keywords')['service']['do_not_use_bhyt'].config('keywords')['error']['in'],

            'allow_send_pacs.integer'               => config('keywords')['service']['allow_send_pacs'].config('keywords')['error']['integer'],
            'allow_send_pacs.in'                    => config('keywords')['service']['allow_send_pacs'].config('keywords')['error']['in'],
            'allow_send_pacs.prohibited_unless'     => config('keywords')['service']['allow_send_pacs'].config('keywords')['error']['prohibited_unless_service_type'].$this->allow_send_pacs_check_string,
        
        
            'other_pay_source_id.integer'              => config('keywords')['service']['other_pay_source_id'].config('keywords')['error']['integer'],
            'other_pay_source_id.exists'               => config('keywords')['service']['other_pay_source_id'].config('keywords')['error']['exists'],

            'attach_assign_print_type_code.string'              => config('keywords')['service']['attach_assign_print_type_code'].config('keywords')['error']['string'],
            'attach_assign_print_type_code.max'                 => config('keywords')['service']['attach_assign_print_type_code'].config('keywords')['error']['string_max'], 

            'description.string'              => config('keywords')['service']['description'].config('keywords')['error']['string'],
            'description.max'                 => config('keywords')['service']['description'].config('keywords')['error']['string_max'], 

            'notice.string'              => config('keywords')['service']['notice'].config('keywords')['error']['string'],
            'notice.max'                 => config('keywords')['service']['notice'].config('keywords')['error']['string_max'], 

            'tax_rate_type.integer'               => config('keywords')['service']['tax_rate_type'].config('keywords')['error']['integer'],
            'tax_rate_type.in'                    => config('keywords')['service']['tax_rate_type'].config('keywords')['error']['in'],

            'process_code.string'              => config('keywords')['service']['process_code'].config('keywords')['error']['string'],
            'process_code.max'                 => config('keywords')['service']['process_code'].config('keywords')['error']['string_max'], 

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('applied_patient_classify_ids')) {
            $this->merge([
                'applied_patient_classify_ids_list' => explode(',', $this->applied_patient_classify_ids),
            ]);
        }
        if ($this->has('applied_patient_type_ids')) {
            $this->merge([
                'applied_patient_type_ids_list' => explode(',', $this->applied_patient_type_ids),
            ]);
        }
        if ($this->has('min_proc_time_except_paty_ids')) {
            $this->merge([
                'min_proc_time_except_paty_ids_list' => explode(',', $this->min_proc_time_except_paty_ids),
            ]);
        }
        if ($this->has('max_proc_time_except_paty_ids')) {
            $this->merge([
                'max_proc_time_except_paty_ids_list' => explode(',', $this->max_proc_time_except_paty_ids),
            ]);
        }
        if ($this->has('total_time_except_paty_ids')) {
            $this->merge([
                'total_time_except_paty_ids_list' => explode(',', $this->total_time_except_paty_ids),
            ]);
        }
        if ($this->has('body_part_ids')) {
            $this->merge([
                'body_part_ids_list' => explode(',', $this->body_part_ids),
            ]);
        }

    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('applied_patient_classify_ids_list') && ($this->applied_patient_classify_ids_list[0] != null)) {
                foreach ($this->applied_patient_classify_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientClassify::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('applied_patient_classify_ids', 'Đối tượng chi tiết với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('applied_patient_type_ids_list') && ($this->applied_patient_type_ids_list[0] != null)) {
                foreach ($this->applied_patient_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('applied_patient_type_ids', 'Đối tượng thanh toán với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('min_proc_time_except_paty_ids_list') && ($this->min_proc_time_except_paty_ids_list[0] != null)) {
                foreach ($this->min_proc_time_except_paty_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('min_proc_time_except_paty_ids', 'Đối tượng thanh toán với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('max_proc_time_except_paty_ids_list') && ($this->max_proc_time_except_paty_ids_list[0] != null)) {
                foreach ($this->max_proc_time_except_paty_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('max_proc_time_except_paty_ids', 'Đối tượng thanh toán với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('total_time_except_paty_ids_list') && ($this->total_time_except_paty_ids_list[0] != null)) {
                foreach ($this->total_time_except_paty_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('total_time_except_paty_ids', 'Đối tượng thanh toán với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('body_part_ids_list') && ($this->body_part_ids_list[0] != null)) {
                foreach ($this->body_part_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\BodyPart::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('body_part_ids', 'Bộ phận cơ thể với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
