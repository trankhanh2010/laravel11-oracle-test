<?php

namespace App\Http\Requests\ServicePaty;

use App\Models\HIS\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CreateServicePatyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected $ration_time_id_check = ['AN'];
    protected $ration_time_id_check_string;
    protected $ration_time_id_check_id;

    protected $instr_num_by_type_from_check = ['KH'];
    protected $instr_num_by_type_from_check_string;
    protected $instr_num_by_type_from_check_id;

    protected $instr_num_by_type_to_check = ['KH'];
    protected $instr_num_by_type_to_check_string;
    protected $instr_num_by_type_to_check_id;
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
        $this->ration_time_id_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->ration_time_id_check)->pluck('id')->implode(',');
        $this->ration_time_id_check_string = implode(", ", $this->ration_time_id_check);

        $this->instr_num_by_type_from_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->instr_num_by_type_from_check)->pluck('id')->implode(',');
        $this->instr_num_by_type_from_check_string = implode(", ", $this->instr_num_by_type_from_check);

        $this->instr_num_by_type_to_check_id = ServiceType::select('id')->whereIn('service_type_code', $this->instr_num_by_type_to_check)->pluck('id')->implode(',');
        $this->instr_num_by_type_to_check_string = implode(", ", $this->instr_num_by_type_to_check);

         // Ép kiểu giá trị sang int nếu nó là số, nếu không thì thành 0
        $service_type_id = is_numeric($this->service_type_id) ? (int) $this->service_type_id : 0;
        $service_id = is_numeric($this->service_id) ? (int) $this->service_id : 0;
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
            'service_id' =>                     [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Service', 'id')
                                                    ->where(function ($query) use($service_type_id){
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                                                        ->where(DB::connection('oracle_his')->raw("service_type_id"), $service_type_id);
                                                    }),
                                                ],
            'branch_ids' =>                     'required|string', 
            'patient_type_ids' =>               'required|string', 
            'patient_classify_id' =>            [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\PatientClassify', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'price' =>                          'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'vat_ratio' =>                      'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|max:1',

            'overtime_price' =>                 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|lte:price',
            'actual_price' =>                   'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'priority' =>                       'nullable|numeric',
            'ration_time_id' =>                 [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\RationTime', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'prohibited_unless:service_type_id,'.$this->ration_time_id_check_id
                                                ], 
            'package_id' =>                     [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Package', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    })
                                                ], 
            'service_condition_id' =>           [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\ServiceCondition', 'id')
                                                    ->where(function ($query) use ($service_id){
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                                                        ->where(DB::connection('oracle_his')->raw("service_id"), $service_id);
                                                    }),
                                                    'declined_if:service_id,null'
                                                ], 

            'intruction_number_from' =>         'nullable|integer|min:0',
            'intruction_number_to' =>           'nullable|integer|min:0',
            'instr_num_by_type_from' =>         'nullable|integer|min:0|prohibited_unless:service_type_id,'.$this->instr_num_by_type_from_check_id,
            'instr_num_by_type_to' =>           'nullable|integer|min:0|prohibited_unless:service_type_id,'.$this->instr_num_by_type_to_check_id,

            'from_time' =>                      'nullable|integer|regex:/^\d{14}$/',
            'to_time' =>                        'nullable|integer|regex:/^\d{14}$/|gte:from_time',
            'treatment_from_time' =>            'nullable|integer|regex:/^\d{14}$/',
            'treatment_to_time' =>              'nullable|integer|regex:/^\d{14}$/|gte:treatment_from_time',
            'day_from' =>                       'nullable|integer|in:1,2,3,4,5,6,7',
            'day_to' =>                         'nullable|integer|in:1,2,3,4,5,6,7',
            'hour_from' =>                      [
                                                    'nullable',
                                                    'string',
                                                    'max:4',
                                                    'regex:/^(0[0-9]|1[0-9]|2[0-3])(00|15|30|45)$/'
                                                ],
            'hour_to' =>                        [
                                                    'nullable',
                                                    'string',
                                                    'max:4',
                                                    'regex:/^(0[0-9]|1[0-9]|2[0-3])(00|15|30|45)$/'
                                                ],

            'execute_room_ids' =>               'nullable|string|max:4000',  
            'request_deparment_ids' =>          'nullable|string|max:4000',                    
            'request_room_ids' =>               'nullable|string|max:4000',                    
                  

        ];
    }

    public function messages()
    {
        return [
            'service_type_id.required'      => config('keywords')['service_paty']['service_type_id'].config('keywords')['error']['required'],
            'service_type_id.integer'       => config('keywords')['service_paty']['service_type_id'].config('keywords')['error']['integer'],
            'service_type_id.exists'        => config('keywords')['service_paty']['service_type_id'].config('keywords')['error']['exists'],  
            
            'service_id.required'      => config('keywords')['service_paty']['service_id'].config('keywords')['error']['required'],
            'service_id.integer'       => config('keywords')['service_paty']['service_id'].config('keywords')['error']['integer'],
            'service_id.exists'        => config('keywords')['service_paty']['service_id'].config('keywords')['error']['exists'].config('keywords')['error']['not_in_service_type_id'], 

            'branch_ids.required'       => config('keywords')['service_paty']['branch_ids'].config('keywords')['error']['required'],
            'branch_ids.string'         => config('keywords')['service_paty']['branch_ids'].config('keywords')['error']['string'],

            'patient_type_ids.required'     => config('keywords')['service_paty']['patient_type_id'].config('keywords')['error']['required'],
            'patient_type_ids.string'       => config('keywords')['service_paty']['patient_type_id'].config('keywords')['error']['string'],

            'patient_classify_id.required'      => config('keywords')['service_paty']['patient_classify_id'].config('keywords')['error']['required'],
            'patient_classify_id.integer'       => config('keywords')['service_paty']['patient_classify_id'].config('keywords')['error']['integer'],
            'patient_classify_id.exists'        => config('keywords')['service_paty']['patient_classify_id'].config('keywords')['error']['exists'], 

            'price.required'         => config('keywords')['service_paty']['price'].config('keywords')['error']['required'],
            'price.numeric'          => config('keywords')['service_paty']['price'].config('keywords')['error']['numeric'],
            'price.regex'            => config('keywords')['service_paty']['price'].config('keywords')['error']['regex_19_4'],
            'price.min'              => config('keywords')['service_paty']['price'].config('keywords')['error']['integer_min'],

            'vat_ratio.required'         => config('keywords')['service_paty']['vat_ratio'].config('keywords')['error']['required'],
            'vat_ratio.numeric'          => config('keywords')['service_paty']['vat_ratio'].config('keywords')['error']['numeric'],
            'vat_ratio.regex'            => config('keywords')['service_paty']['vat_ratio'].config('keywords')['error']['regex_19_4'],
            'vat_ratio.min'              => config('keywords')['service_paty']['vat_ratio'].config('keywords')['error']['integer_min'],
            'vat_ratio.max'              => config('keywords')['service_paty']['vat_ratio'].config('keywords')['error']['integer_max'],


            'overtime_price.required'         => config('keywords')['service_paty']['overtime_price'].config('keywords')['error']['required'],
            'overtime_price.numeric'          => config('keywords')['service_paty']['overtime_price'].config('keywords')['error']['numeric'],
            'overtime_price.regex'            => config('keywords')['service_paty']['overtime_price'].config('keywords')['error']['regex_19_4'],
            'overtime_price.min'              => config('keywords')['service_paty']['overtime_price'].config('keywords')['error']['integer_min'],
            'overtime_price.lte'              => config('keywords')['service_paty']['overtime_price'].config('keywords')['error']['lte'],

            'actual_price.required'         => config('keywords')['service_paty']['actual_price'].config('keywords')['error']['required'],
            'actual_price.numeric'          => config('keywords')['service_paty']['actual_price'].config('keywords')['error']['numeric'],
            'actual_price.regex'            => config('keywords')['service_paty']['actual_price'].config('keywords')['error']['regex_19_4'],
            'actual_price.min'              => config('keywords')['service_paty']['actual_price'].config('keywords')['error']['integer_min'],

            'priority.numeric'          => config('keywords')['service_paty']['priority'].config('keywords')['error']['numeric'],

            'ration_time_id.integer'            => config('keywords')['service_paty']['ration_time_id'].config('keywords')['error']['integer'],
            'ration_time_id.exists'             => config('keywords')['service_paty']['ration_time_id'].config('keywords')['error']['exists'], 
            'ration_time_id.prohibited_unless'  => config('keywords')['service_paty']['ration_time_id'].config('keywords')['error']['prohibited_unless_service_type'].$this->ration_time_id_check_string,

            'package_id.integer'            => config('keywords')['service_paty']['package_id'].config('keywords')['error']['integer'],
            'package_id.exists'             => config('keywords')['service_paty']['package_id'].config('keywords')['error']['exists'], 

            'service_condition_id.integer'            => config('keywords')['service_paty']['service_condition_id'].config('keywords')['error']['integer'],
            'service_condition_id.exists'             => config('keywords')['service_paty']['service_condition_id'].config('keywords')['error']['exists'].config('keywords')['error']['not_in_service_id'], 
            'service_condition_id.declined_if'        => config('keywords')['service_paty']['service_condition_id'].config('keywords')['error']['declined_if'].config('keywords')['service_paty']['service_id'].' đã được nhập!', 

            'intruction_number_from.integer'        => config('keywords')['service_paty']['intruction_number_from'].config('keywords')['error']['integer'],
            'intruction_number_from.min'            => config('keywords')['service_paty']['intruction_number_from'].config('keywords')['error']['integer_min'], 

            'intruction_number_to.integer'      => config('keywords')['service_paty']['intruction_number_to'].config('keywords')['error']['integer'],
            'intruction_number_to.min'          => config('keywords')['service_paty']['intruction_number_to'].config('keywords')['error']['integer_min'], 

            'instr_num_by_type_from.integer'            => config('keywords')['service_paty']['instr_num_by_type_from'].config('keywords')['error']['integer'],
            'instr_num_by_type_from.min'                => config('keywords')['service_paty']['instr_num_by_type_from'].config('keywords')['error']['integer_min'], 
            'instr_num_by_type_from.prohibited_unless'  => config('keywords')['service_paty']['instr_num_by_type_from'].config('keywords')['error']['prohibited_unless_service_type'].$this->instr_num_by_type_from_check_string,

            'instr_num_by_type_to.integer'            => config('keywords')['service_paty']['instr_num_by_type_to'].config('keywords')['error']['integer'],
            'instr_num_by_type_to.min'                => config('keywords')['service_paty']['instr_num_by_type_to'].config('keywords')['error']['integer_min'], 
            'instr_num_by_type_to.prohibited_unless'  => config('keywords')['service_paty']['instr_num_by_type_to'].config('keywords')['error']['prohibited_unless_service_type'].$this->instr_num_by_type_to_check_string,


            'from_time.integer'            => config('keywords')['service_paty']['from_time'].config('keywords')['error']['integer'],
            'from_time.regex'              => config('keywords')['service_paty']['from_time'].config('keywords')['error']['regex_ymdhis'],

            'to_time.integer'            => config('keywords')['service_paty']['to_time'].config('keywords')['error']['integer'],
            'to_time.regex'              => config('keywords')['service_paty']['to_time'].config('keywords')['error']['regex_ymdhis'],
            'to_time.gte'                => config('keywords')['service_paty']['to_time'].config('keywords')['error']['gte'],

            'treatment_from_time.integer'            => config('keywords')['service_paty']['treatment_from_time'].config('keywords')['error']['integer'],
            'treatment_from_time.regex'              => config('keywords')['service_paty']['treatment_from_time'].config('keywords')['error']['regex_ymdhis'],

            'treatment_to_time.integer'            => config('keywords')['service_paty']['treatment_to_time'].config('keywords')['error']['integer'],
            'treatment_to_time.regex'              => config('keywords')['service_paty']['treatment_to_time'].config('keywords')['error']['regex_ymdhis'],
            'treatment_to_time.gte'                => config('keywords')['service_paty']['treatment_to_time'].config('keywords')['error']['gte'],

            'day_from.integer'             => config('keywords')['service_paty']['day_from'].config('keywords')['error']['integer'],
            'day_from.in'                  => config('keywords')['service_paty']['day_from'].config('keywords')['error']['in'],

            'day_to.integer'             => config('keywords')['service_paty']['day_to'].config('keywords')['error']['integer'],
            'day_to.in'                  => config('keywords')['service_paty']['day_to'].config('keywords')['error']['in'],

            'hour_from.string'                  => config('keywords')['service_paty']['hour_from'].config('keywords')['error']['string'],
            'hour_from.max'                     => config('keywords')['service_paty']['hour_from'].config('keywords')['error']['string_max'],
            'hour_from.regex'                   => config('keywords')['service_paty']['hour_from'].config('keywords')['error']['regex_hhmm'],

            'hour_to.string'                  => config('keywords')['service_paty']['hour_to'].config('keywords')['error']['string'],
            'hour_to.max'                     => config('keywords')['service_paty']['hour_to'].config('keywords')['error']['string_max'],
            'hour_to.regex'                   => config('keywords')['service_paty']['hour_to'].config('keywords')['error']['regex_hhmm'],

            'request_deparment_ids.string'                  => config('keywords')['service_paty']['request_deparment_ids'].config('keywords')['error']['string'],
            'request_deparment_ids.max'                     => config('keywords')['service_paty']['request_deparment_ids'].config('keywords')['error']['string_max'],

            'request_room_ids.string'                  => config('keywords')['service_paty']['request_room_ids'].config('keywords')['error']['string'],
            'request_room_ids.max'                     => config('keywords')['service_paty']['request_room_ids'].config('keywords')['error']['string_max'],

            'execute_room_ids.string'                  => config('keywords')['service_paty']['execute_room_ids'].config('keywords')['error']['string'],
            'execute_room_ids.max'                     => config('keywords')['service_paty']['execute_room_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('branch_ids')) {
            $this->merge([
                'branch_ids_list' => explode(',', $this->branch_ids),
            ]);
        }
        if ($this->has('patient_type_ids')) {
            $this->merge([
                'patient_type_ids_list' => explode(',', $this->patient_type_ids),
            ]);
        }
        if ($this->has('request_room_ids')) {
            $this->merge([
                'request_room_ids_list' => explode(',', $this->request_room_ids),
            ]);
        }
        if ($this->has('request_deparment_ids')) {
            $this->merge([
                'request_deparment_ids_list' => explode(',', $this->request_deparment_ids),
            ]);
        }
        if ($this->has('execute_room_ids')) {
            $this->merge([
                'execute_room_ids_list' => explode(',', $this->execute_room_ids),
            ]);
        }
    }
     
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('branch_ids_list') && ($this->branch_ids_list[0] != null)) {
                foreach ($this->branch_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Branch::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('branch_ids', 'Chi nhánh với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('patient_type_ids_list') && ($this->patient_type_ids_list[0] != null)) {
                foreach ($this->patient_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('patient_type_ids', 'Đối tượng thanh toán với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('request_room_ids_list') && ($this->request_room_ids_list[0] != null)) {
                foreach ($this->request_room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('request_room_ids', 'Phòng yêu cầu với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('request_deparment_ids_list') && ($this->request_deparment_ids_list[0] != null)) {
                foreach ($this->request_deparment_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Department::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('request_deparment_ids', 'Khoa yêu cầu với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('execute_room_ids_list') && ($this->execute_room_ids_list[0] != null)) {
                foreach ($this->execute_room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('execute_room_ids', 'Phòng thực hiện với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
