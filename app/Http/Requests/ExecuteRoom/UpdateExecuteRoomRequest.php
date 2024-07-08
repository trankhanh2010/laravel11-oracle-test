<?php

namespace App\Http\Requests\ExecuteRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
class UpdateExecuteRoomRequest extends FormRequest
{
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
        return [
            'execute_room_name' =>              'required|string|max:100',
            'room_group_id' =>                  'nullable|integer|exists:App\Models\HIS\RoomGroup,id',
            'room_type_id'  =>                  'required|integer|exists:App\Models\HIS\RoomType,id',
            'order_issue_code' =>               'nullable|string|max:10',
            'num_order' =>                      'nullable|integer',
            'test_type_code' =>                 'nullable|string|max:20|exists:App\Models\HIS\TestType,test_type_code',
            'max_request_by_day' =>             'nullable|integer|min:0',
            'max_appointment_by_day' =>         'nullable|integer|min:0',
            'hold_order' =>                     'nullable|integer',
            'speciality_id' =>                  'nullable|integer|exists:App\Models\HIS\Speciality,id',
            'address' =>                        'nullable|string|max:200',
            'max_req_bhyt_by_day' =>            'nullable|integer|min:0',
            'max_patient_by_day' =>             'nullable|integer|min:0',
            'average_eta' =>                    'nullable|integer|min:0',
            'responsible_loginname' =>          'nullable|string|max:50|exists:App\Models\HIS\Employee,loginname',
            'responsible_username' =>           'nullable|string|max:100|exists:App\Models\HIS\Employee,tdl_username',
            'default_instr_patient_type_id' =>  'nullable|integer|exists:App\Models\HIS\PatientType,id',
            'default_drug_store_ids' =>         'nullable|string|max:100',
            'default_cashier_room_id' =>        'nullable|integer|exists:App\Models\HIS\CashierRoom,id',
            'area_id' =>                        'nullable|integer|exists:App\Models\HIS\Area,id',
            'screen_saver_module_link' =>       'nullable|string|max:200|exists:App\Models\ACS\Module,module_link',
            'bhyt_code' =>                      'nullable|string|max:50',
            'deposit_account_book_id' =>        'nullable|integer|exists:App\Models\HIS\AccountBook,id',
            'bill_account_book_id' =>           'nullable|integer|exists:App\Models\HIS\AccountBook,id',
            'is_emergency' =>                   'nullable|integer|in:0,1',
            'is_exam' =>                        'nullable|integer|in:0,1',
            'is_speciality' =>                  'nullable|integer|in:0,1',
            'is_use_kiosk' =>                   'nullable|integer|in:0,1',
            'is_restrict_execute_room' =>       'nullable|integer|in:0,1',
            'is_restrict_time' =>               'nullable|integer|in:0,1',
            'is_vaccine' =>                     'nullable|integer|in:0,1',
            'is_restrict_req_service' =>        'nullable|integer|in:0,1',
            'allow_not_choose_service' =>       'nullable|integer|in:0,1',
            'is_kidney' =>                      'nullable|integer|in:0,1',
            'kidney_shift_count' =>             'required_with:is_kidney|nullable|integer|in:0,1',
            'is_surgery' =>                     'nullable|integer|in:0,1',
            'is_auto_expend_add_exam' =>        'nullable|integer|in:0,1',
            'is_allow_no_icd' =>                'nullable|integer|in:0,1',
            'is_pause' =>                       'nullable|integer|in:0,1',
            'is_restrict_medicine_type' =>      'nullable|integer|in:0,1',
            'is_pause_enclitic' =>              'nullable|integer|in:0,1',
            'is_vitamin_a' =>                   'nullable|integer|in:0,1',
            'is_restrict_patient_type' =>       'nullable|integer|in:0,1',
            'is_block_num_order' =>             'nullable|integer|in:0,1',
            'default_service_id' =>             'nullable|integer|exists:App\Models\HIS\Service,id',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'execute_room_name.required'    => config('keywords')['execute_room']['execute_room_name'].config('keywords')['error']['required'],
            'execute_room_name.string'      => config('keywords')['execute_room']['execute_room_name'].config('keywords')['error']['string'],
            'execute_room_name.max'         => config('keywords')['execute_room']['execute_room_name'].config('keywords')['error']['string_max'],

            'room_group_id.integer'     => config('keywords')['execute_room']['room_group_id'].config('keywords')['error']['integer'],
            'room_group_id.exists'      => config('keywords')['execute_room']['room_group_id'].config('keywords')['error']['exists'],  

            'room_type_id.required'    => config('keywords')['execute_room']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['execute_room']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['execute_room']['room_type_id'].config('keywords')['error']['exists'],  

            'order_issue_code.string'      => config('keywords')['execute_room']['order_issue_code'].config('keywords')['error']['string'],
            'order_issue_code.max'         => config('keywords')['execute_room']['order_issue_code'].config('keywords')['error']['string_max'],

            'num_order.integer'     => config('keywords')['execute_room']['num_order'].config('keywords')['error']['integer'],

            'test_type_code.string'      => config('keywords')['execute_room']['test_type_code'].config('keywords')['error']['string'],
            'test_type_code.max'         => config('keywords')['execute_room']['test_type_code'].config('keywords')['error']['string_max'],
            'test_type_code.exists'      => config('keywords')['execute_room']['test_type_code'].config('keywords')['error']['exists'],  

            'max_request_by_day.integer'     => config('keywords')['execute_room']['max_request_by_day'].config('keywords')['error']['integer'],
            'max_request_by_day.min'         => config('keywords')['execute_room']['max_request_by_day'].config('keywords')['error']['integer_min'],

            'max_appointment_by_day.integer'     => config('keywords')['execute_room']['max_appointment_by_day'].config('keywords')['error']['integer'],
            'max_appointment_by_day.min'         => config('keywords')['execute_room']['max_appointment_by_day'].config('keywords')['error']['integer_min'],

            'hold_order.integer'     => config('keywords')['execute_room']['hold_order'].config('keywords')['error']['integer'],

            'speciality_id.integer'     => config('keywords')['execute_room']['speciality_id'].config('keywords')['error']['integer'],
            'speciality_id.exists'      => config('keywords')['execute_room']['speciality_id'].config('keywords')['error']['exists'],  

            'address.string'      => config('keywords')['execute_room']['address'].config('keywords')['error']['string'],
            'address.max'         => config('keywords')['execute_room']['address'].config('keywords')['error']['string_max'],

            'max_req_bhyt_by_day.integer'     => config('keywords')['execute_room']['max_req_bhyt_by_day'].config('keywords')['error']['integer'],
            'max_req_bhyt_by_day.min'         => config('keywords')['execute_room']['max_req_bhyt_by_day'].config('keywords')['error']['integer_min'],

            'max_patient_by_day.integer'     => config('keywords')['execute_room']['max_patient_by_day'].config('keywords')['error']['integer'],
            'max_patient_by_day.min'         => config('keywords')['execute_room']['max_patient_by_day'].config('keywords')['error']['integer_min'],

            'average_eta.integer'     => config('keywords')['execute_room']['average_eta'].config('keywords')['error']['integer'],
            'average_eta.min'         => config('keywords')['execute_room']['average_eta'].config('keywords')['error']['integer_min'],

            'responsible_loginname.string'      => config('keywords')['execute_room']['responsible_loginname'].config('keywords')['error']['string'],
            'responsible_loginname.max'         => config('keywords')['execute_room']['responsible_loginname'].config('keywords')['error']['string_max'],
            'responsible_loginname.exists'      => config('keywords')['execute_room']['responsible_loginname'].config('keywords')['error']['exists'],  

            'responsible_username.string'      => config('keywords')['execute_room']['responsible_username'].config('keywords')['error']['string'],
            'responsible_username.max'         => config('keywords')['execute_room']['responsible_username'].config('keywords')['error']['string_max'],
            'responsible_username.exists'      => config('keywords')['execute_room']['responsible_username'].config('keywords')['error']['exists'],  

            'default_instr_patient_type_id.integer'     => config('keywords')['execute_room']['default_instr_patient_type_id'].config('keywords')['error']['integer'],
            'default_instr_patient_type_id.exists'      => config('keywords')['execute_room']['default_instr_patient_type_id'].config('keywords')['error']['exists'],  
        
            'default_drug_store_ids.string'      => config('keywords')['execute_room']['default_drug_store_ids'].config('keywords')['error']['string'],
            'default_drug_store_ids.max'      => config('keywords')['execute_room']['default_drug_store_ids'].config('keywords')['error']['string_max'],

            'default_cashier_room_id.integer'     => config('keywords')['execute_room']['default_cashier_room_id'].config('keywords')['error']['integer'],
            'default_cashier_room_id.exists'      => config('keywords')['execute_room']['default_cashier_room_id'].config('keywords')['error']['exists'],  

            'area_id.integer'     => config('keywords')['execute_room']['area_id'].config('keywords')['error']['integer'],
            'area_id.exists'      => config('keywords')['execute_room']['area_id'].config('keywords')['error']['exists'],  
            
            'screen_saver_module_link.string'      => config('keywords')['execute_room']['screen_saver_module_link'].config('keywords')['error']['string'],
            'screen_saver_module_link.max'         => config('keywords')['execute_room']['screen_saver_module_link'].config('keywords')['error']['string_max'],
            'screen_saver_module_link.exists'      => config('keywords')['execute_room']['screen_saver_module_link'].config('keywords')['error']['exists'],  

            'bhyt_code.string'      => config('keywords')['execute_room']['bhyt_code'].config('keywords')['error']['string'],
            'bhyt_code.max'         => config('keywords')['execute_room']['bhyt_code'].config('keywords')['error']['string_max'],
            
            'deposit_account_book_id.integer'     => config('keywords')['execute_room']['deposit_account_book_id'].config('keywords')['error']['integer'],
            'deposit_account_book_id.exists'      => config('keywords')['execute_room']['deposit_account_book_id'].config('keywords')['error']['exists'],  

            'bill_account_book_id.integer'     => config('keywords')['execute_room']['bill_account_book_id'].config('keywords')['error']['integer'],
            'bill_account_book_id.exists'      => config('keywords')['execute_room']['bill_account_book_id'].config('keywords')['error']['exists'],  

            'is_emergency.integer'    => config('keywords')['execute_room']['is_emergency'].config('keywords')['error']['integer'],
            'is_emergency.in'         => config('keywords')['execute_room']['is_emergency'].config('keywords')['error']['in'], 
            
            'is_exam.integer'    => config('keywords')['execute_room']['is_exam'].config('keywords')['error']['integer'],
            'is_exam.in'         => config('keywords')['execute_room']['is_exam'].config('keywords')['error']['in'], 
            
            'is_speciality.integer'    => config('keywords')['execute_room']['is_speciality'].config('keywords')['error']['integer'],
            'is_speciality.in'         => config('keywords')['execute_room']['is_speciality'].config('keywords')['error']['in'], 
            
            'is_use_kiosk.integer'    => config('keywords')['execute_room']['is_use_kiosk'].config('keywords')['error']['integer'],
            'is_use_kiosk.in'         => config('keywords')['execute_room']['is_use_kiosk'].config('keywords')['error']['in'], 
            
            'is_restrict_execute_room.integer'    => config('keywords')['execute_room']['is_restrict_execute_room'].config('keywords')['error']['integer'],
            'is_restrict_execute_room.in'         => config('keywords')['execute_room']['is_restrict_execute_room'].config('keywords')['error']['in'], 
            
            'is_restrict_time.integer'    => config('keywords')['execute_room']['is_restrict_time'].config('keywords')['error']['integer'],
            'is_restrict_time.in'         => config('keywords')['execute_room']['is_restrict_time'].config('keywords')['error']['in'], 
            
            'is_vaccine.integer'    => config('keywords')['execute_room']['is_vaccine'].config('keywords')['error']['integer'],
            'is_vaccine.in'         => config('keywords')['execute_room']['is_vaccine'].config('keywords')['error']['in'], 
            
            'is_restrict_req_service.integer'    => config('keywords')['execute_room']['is_restrict_req_service'].config('keywords')['error']['integer'],
            'is_restrict_req_service.in'         => config('keywords')['execute_room']['is_restrict_req_service'].config('keywords')['error']['in'], 
            
            'allow_not_choose_service.integer'    => config('keywords')['execute_room']['allow_not_choose_service'].config('keywords')['error']['integer'],
            'allow_not_choose_service.in'         => config('keywords')['execute_room']['allow_not_choose_service'].config('keywords')['error']['in'], 
            
            'is_kidney.integer'    => config('keywords')['execute_room']['is_kidney'].config('keywords')['error']['integer'],
            'is_kidney.in'         => config('keywords')['execute_room']['is_kidney'].config('keywords')['error']['in'], 

            'kidney_shift_count.required_with'      => config('keywords')['execute_room']['kidney_shift_count'].' chỉ được nhập khi '.config('keywords')['execute_room']['is_kidney'].' được chọn!',
            'kidney_shift_count.integer'            => config('keywords')['execute_room']['kidney_shift_count'].config('keywords')['error']['integer'],
            'kidney_shift_count.in'                 => config('keywords')['execute_room']['kidney_shift_count'].config('keywords')['error']['in'], 
            
            'is_surgery.integer'    => config('keywords')['execute_room']['is_surgery'].config('keywords')['error']['integer'],
            'is_surgery.in'         => config('keywords')['execute_room']['is_surgery'].config('keywords')['error']['in'], 
            
            'is_auto_expend_add_exam.integer'    => config('keywords')['execute_room']['is_auto_expend_add_exam'].config('keywords')['error']['integer'],
            'is_auto_expend_add_exam.in'         => config('keywords')['execute_room']['is_auto_expend_add_exam'].config('keywords')['error']['in'], 
            
            'is_allow_no_icd.integer'    => config('keywords')['execute_room']['is_allow_no_icd'].config('keywords')['error']['integer'],
            'is_allow_no_icd.in'         => config('keywords')['execute_room']['is_allow_no_icd'].config('keywords')['error']['in'], 
            
            'is_pause.integer'    => config('keywords')['execute_room']['is_pause'].config('keywords')['error']['integer'],
            'is_pause.in'         => config('keywords')['execute_room']['is_pause'].config('keywords')['error']['in'], 
            
            'is_restrict_medicine_type.integer'    => config('keywords')['execute_room']['is_restrict_medicine_type'].config('keywords')['error']['integer'],
            'is_restrict_medicine_type.in'         => config('keywords')['execute_room']['is_restrict_medicine_type'].config('keywords')['error']['in'], 
            
            'is_pause_enclitic.integer'    => config('keywords')['execute_room']['is_pause_enclitic'].config('keywords')['error']['integer'],
            'is_pause_enclitic.in'         => config('keywords')['execute_room']['is_pause_enclitic'].config('keywords')['error']['in'], 
            
            'is_vitamin_a.integer'    => config('keywords')['execute_room']['is_vitamin_a'].config('keywords')['error']['integer'],
            'is_vitamin_a.in'         => config('keywords')['execute_room']['is_vitamin_a'].config('keywords')['error']['in'], 
            
            'is_restrict_patient_type.integer'    => config('keywords')['execute_room']['is_restrict_patient_type'].config('keywords')['error']['integer'],
            'is_restrict_patient_type.in'         => config('keywords')['execute_room']['is_restrict_patient_type'].config('keywords')['error']['in'], 
            
            'is_block_num_order.integer'    => config('keywords')['execute_room']['is_block_num_order'].config('keywords')['error']['integer'],
            'is_block_num_order.in'         => config('keywords')['execute_room']['is_block_num_order'].config('keywords')['error']['in'], 

            'default_service_id.integer'     => config('keywords')['execute_room']['default_service_id'].config('keywords')['error']['integer'],
            'default_service_id.exists'      => config('keywords')['execute_room']['default_service_id'].config('keywords')['error']['exists'],  

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('default_drug_store_ids')) {
            $this->merge([
                'default_drug_store_ids_list' => explode(',', $this->default_drug_store_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('default_drug_store_ids') && (strlen($this->default_drug_store_ids) >= 100)) {
                $validator->errors()->add('default_drug_store_ids', config('keywords')['execute_room']['default_drug_store_ids'].' tối đa 100 kí tự!');
            }
            if ($this->has('default_drug_store_ids_list') && ($this->default_drug_store_ids_list[0] != null)) {
                foreach ($this->default_drug_store_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MediStock::find($id)) {
                        $validator->errors()->add('default_drug_store_ids', 'Nhà thuốc với id = ' . $id . ' trong danh sách nhà thuốc không tồn tại!');
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
