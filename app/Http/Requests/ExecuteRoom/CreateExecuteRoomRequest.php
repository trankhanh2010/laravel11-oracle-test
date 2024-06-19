<?php

namespace App\Http\Requests\ExecuteRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class CreateExecuteRoomRequest extends FormRequest
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
            'execute_room_code' =>              'required|string|max:20|unique:App\Models\HIS\ExecuteRoom,execute_room_code',
            'execute_room_name' =>              'required|string|max:100',
            'department_id' =>                  'required|integer|exists:App\Models\HIS\Department,id',
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
        ];
    }
    public function messages()
    {
        return [
            'execute_room_code.required'    => config('keywords')['execute_room']['execute_room_code'].' không được bỏ trống!',
            'execute_room_code.string'      => config('keywords')['execute_room']['execute_room_code'].' phải là chuỗi string!',
            'execute_room_code.max'         => config('keywords')['execute_room']['execute_room_code'].' tối đa 20 kí tự!',
            'execute_room_code.unique'      => config('keywords')['execute_room']['execute_room_code'].' = '. $this->execute_room_code . ' đã tồn tại!',

            'execute_room_name.required'    => config('keywords')['execute_room']['execute_room_name'].' không được bỏ trống!',
            'execute_room_name.string'      => config('keywords')['execute_room']['execute_room_name'].' phải là chuỗi string!',
            'execute_room_name.max'         => config('keywords')['execute_room']['execute_room_name'].' tối đa 100 kí tự!',

            'department_id.required'    => config('keywords')['execute_room']['department_id'].' không được bỏ trống!',            
            'department_id.integer'     => config('keywords')['execute_room']['department_id'].' phải là số nguyên!',
            'department_id.exists'      => config('keywords')['execute_room']['department_id'].' = '.$this->department_id.' không tồn tại!',  

            'room_group_id.integer'     => config('keywords')['execute_room']['room_group_id'].' phải là số nguyên!',
            'room_group_id.exists'      => config('keywords')['execute_room']['room_group_id'].' = '.$this->room_group_id.' không tồn tại!',  

            'room_type_id.required'    => config('keywords')['execute_room']['room_type_id'].' không được bỏ trống!',            
            'room_type_id.integer'     => config('keywords')['execute_room']['room_type_id'].' phải là số nguyên!',
            'room_type_id.exists'      => config('keywords')['execute_room']['room_type_id'].' = '.$this->room_type_id.' không tồn tại!',  

            'order_issue_code.string'      => config('keywords')['execute_room']['order_issue_code'].' phải là chuỗi string!',
            'order_issue_code.max'         => config('keywords')['execute_room']['order_issue_code'].' tối đa 10 kí tự!',

            'num_order.integer'     => config('keywords')['execute_room']['num_order'].' phải là số nguyên!',

            'test_type_code.string'      => config('keywords')['execute_room']['test_type_code'].' phải là chuỗi string!',
            'test_type_code.max'         => config('keywords')['execute_room']['test_type_code'].' tối đa 20 kí tự!',
            'test_type_code.exists'      => config('keywords')['execute_room']['test_type_code'].' = '.$this->test_type_code.' không tồn tại!',  

            'max_request_by_day.integer'     => config('keywords')['execute_room']['max_request_by_day'].' phải là số nguyên!',
            'max_request_by_day.max'         => config('keywords')['execute_room']['max_request_by_day'].' lớn hơn bằng 0!',

            'max_appointment_by_day.integer'     => config('keywords')['execute_room']['max_appointment_by_day'].' phải là số nguyên!',
            'max_appointment_by_day.max'         => config('keywords')['execute_room']['max_appointment_by_day'].' lớn hơn bằng 0!',

            'hold_order.integer'     => config('keywords')['execute_room']['hold_order'].' phải là số nguyên!',

            'speciality_id.integer'     => config('keywords')['execute_room']['speciality_id'].' phải là số nguyên!',
            'speciality_id.exists'      => config('keywords')['execute_room']['speciality_id'].' = '.$this->speciality_id.' không tồn tại!',  

            'address.string'      => config('keywords')['execute_room']['address'].' phải là chuỗi string!',
            'address.max'         => config('keywords')['execute_room']['address'].' tối đa 200 kí tự!',

            'max_req_bhyt_by_day.integer'     => config('keywords')['execute_room']['max_req_bhyt_by_day'].' phải là số nguyên!',
            'max_req_bhyt_by_day.max'         => config('keywords')['execute_room']['max_req_bhyt_by_day'].' lớn hơn bằng 0!',

            'max_patient_by_day.integer'     => config('keywords')['execute_room']['max_patient_by_day'].' phải là số nguyên!',
            'max_patient_by_day.max'         => config('keywords')['execute_room']['max_patient_by_day'].' lớn hơn bằng 0!',

            'average_eta.integer'     => config('keywords')['execute_room']['average_eta'].' phải là số nguyên!',
            'average_eta.max'         => config('keywords')['execute_room']['average_eta'].' lớn hơn bằng 0!',

            'responsible_loginname.string'      => config('keywords')['execute_room']['responsible_loginname'].' phải là chuỗi string!',
            'responsible_loginname.max'         => config('keywords')['execute_room']['responsible_loginname'].' tối đa 50 kí tự!',
            'responsible_loginname.exists'      => config('keywords')['execute_room']['responsible_loginname'].' = '.$this->responsible_loginname.' không tồn tại!',  

            'responsible_username.string'      => config('keywords')['execute_room']['responsible_username'].' phải là chuỗi string!',
            'responsible_username.max'         => config('keywords')['execute_room']['responsible_username'].' tối đa 100 kí tự!',
            'responsible_username.exists'      => config('keywords')['execute_room']['responsible_username'].' = '.$this->responsible_username.' không tồn tại!',  

            'default_instr_patient_type_id.integer'     => config('keywords')['execute_room']['default_instr_patient_type_id'].' phải là số nguyên!',
            'default_instr_patient_type_id.exists'      => config('keywords')['execute_room']['default_instr_patient_type_id'].' = '.$this->default_instr_patient_type_id.' không tồn tại!',  
        
            'default_drug_store_ids.string'      => config('keywords')['execute_room']['default_drug_store_ids'].' phải là chuỗi string!',

            'default_cashier_room_id.integer'     => config('keywords')['execute_room']['default_cashier_room_id'].' phải là số nguyên!',
            'default_cashier_room_id.exists'      => config('keywords')['execute_room']['default_cashier_room_id'].' = '.$this->default_cashier_room_id.' không tồn tại!',  

            'area_id.integer'     => config('keywords')['execute_room']['area_id'].' phải là số nguyên!',
            'area_id.exists'      => config('keywords')['execute_room']['area_id'].' = '.$this->area_id.' không tồn tại!',  
            
            'screen_saver_module_link.string'      => config('keywords')['execute_room']['screen_saver_module_link'].' phải là chuỗi string!',
            'screen_saver_module_link.max'         => config('keywords')['execute_room']['screen_saver_module_link'].' tối đa 200 kí tự!',
            'screen_saver_module_link.exists'      => config('keywords')['execute_room']['screen_saver_module_link'].' = '.$this->screen_saver_module_link.' không tồn tại!',  

            'bhyt_code.string'      => config('keywords')['execute_room']['bhyt_code'].' phải là chuỗi string!',
            'bhyt_code.max'         => config('keywords')['execute_room']['bhyt_code'].' tối đa 50 kí tự!',
            
            'deposit_account_book_id.integer'     => config('keywords')['execute_room']['deposit_account_book_id'].' phải là số nguyên!',
            'deposit_account_book_id.exists'      => config('keywords')['execute_room']['deposit_account_book_id'].' = '.$this->deposit_account_book_id.' không tồn tại!',  

            'bill_account_book_id.integer'     => config('keywords')['execute_room']['bill_account_book_id'].' phải là số nguyên!',
            'bill_account_book_id.exists'      => config('keywords')['execute_room']['bill_account_book_id'].' = '.$this->bill_account_book_id.' không tồn tại!',  

            'is_emergency.integer'    => config('keywords')['execute_room']['is_emergency'].' phải là số nguyên!',
            'is_emergency.in'         => config('keywords')['execute_room']['is_emergency'].' phải là 0 hoặc 1!', 
            
            'is_exam.integer'    => config('keywords')['execute_room']['is_exam'].' phải là số nguyên!',
            'is_exam.in'         => config('keywords')['execute_room']['is_exam'].' phải là 0 hoặc 1!', 
            
            'is_speciality.integer'    => config('keywords')['execute_room']['is_speciality'].' phải là số nguyên!',
            'is_speciality.in'         => config('keywords')['execute_room']['is_speciality'].' phải là 0 hoặc 1!', 
            
            'is_use_kiosk.integer'    => config('keywords')['execute_room']['is_use_kiosk'].' phải là số nguyên!',
            'is_use_kiosk.in'         => config('keywords')['execute_room']['is_use_kiosk'].' phải là 0 hoặc 1!', 
            
            'is_restrict_execute_room.integer'    => config('keywords')['execute_room']['is_restrict_execute_room'].' phải là số nguyên!',
            'is_restrict_execute_room.in'         => config('keywords')['execute_room']['is_restrict_execute_room'].' phải là 0 hoặc 1!', 
            
            'is_restrict_time.integer'    => config('keywords')['execute_room']['is_restrict_time'].' phải là số nguyên!',
            'is_restrict_time.in'         => config('keywords')['execute_room']['is_restrict_time'].' phải là 0 hoặc 1!', 
            
            'is_vaccine.integer'    => config('keywords')['execute_room']['is_vaccine'].' phải là số nguyên!',
            'is_vaccine.in'         => config('keywords')['execute_room']['is_vaccine'].' phải là 0 hoặc 1!', 
            
            'is_restrict_req_service.integer'    => config('keywords')['execute_room']['is_restrict_req_service'].' phải là số nguyên!',
            'is_restrict_req_service.in'         => config('keywords')['execute_room']['is_restrict_req_service'].' phải là 0 hoặc 1!', 
            
            'allow_not_choose_service.integer'    => config('keywords')['execute_room']['allow_not_choose_service'].' phải là số nguyên!',
            'allow_not_choose_service.in'         => config('keywords')['execute_room']['allow_not_choose_service'].' phải là 0 hoặc 1!', 
            
            'is_kidney.integer'    => config('keywords')['execute_room']['is_kidney'].' phải là số nguyên!',
            'is_kidney.in'         => config('keywords')['execute_room']['is_kidney'].' phải là 0 hoặc 1!', 

            'kidney_shift_count.required_with'      => config('keywords')['execute_room']['kidney_shift_count'].' chỉ được nhập khi '.config('keywords')['execute_room']['is_kidney'].' được chọn!',
            'kidney_shift_count.integer'            => config('keywords')['execute_room']['kidney_shift_count'].' phải là số nguyên!',
            'kidney_shift_count.in'                 => config('keywords')['execute_room']['kidney_shift_count'].' phải là 0 hoặc 1!', 
            
            'is_surgery.integer'    => config('keywords')['execute_room']['is_surgery'].' phải là số nguyên!',
            'is_surgery.in'         => config('keywords')['execute_room']['is_surgery'].' phải là 0 hoặc 1!', 
            
            'is_auto_expend_add_exam.integer'    => config('keywords')['execute_room']['is_auto_expend_add_exam'].' phải là số nguyên!',
            'is_auto_expend_add_exam.in'         => config('keywords')['execute_room']['is_auto_expend_add_exam'].' phải là 0 hoặc 1!', 
            
            'is_allow_no_icd.integer'    => config('keywords')['execute_room']['is_allow_no_icd'].' phải là số nguyên!',
            'is_allow_no_icd.in'         => config('keywords')['execute_room']['is_allow_no_icd'].' phải là 0 hoặc 1!', 
            
            'is_pause.integer'    => config('keywords')['execute_room']['is_pause'].' phải là số nguyên!',
            'is_pause.in'         => config('keywords')['execute_room']['is_pause'].' phải là 0 hoặc 1!', 
            
            'is_restrict_medicine_type.integer'    => config('keywords')['execute_room']['is_restrict_medicine_type'].' phải là số nguyên!',
            'is_restrict_medicine_type.in'         => config('keywords')['execute_room']['is_restrict_medicine_type'].' phải là 0 hoặc 1!', 
            
            'is_pause_enclitic.integer'    => config('keywords')['execute_room']['is_pause_enclitic'].' phải là số nguyên!',
            'is_pause_enclitic.in'         => config('keywords')['execute_room']['is_pause_enclitic'].' phải là 0 hoặc 1!', 
            
            'is_vitamin_a.integer'    => config('keywords')['execute_room']['is_vitamin_a'].' phải là số nguyên!',
            'is_vitamin_a.in'         => config('keywords')['execute_room']['is_vitamin_a'].' phải là 0 hoặc 1!', 
            
            'is_restrict_patient_type.integer'    => config('keywords')['execute_room']['is_restrict_patient_type'].' phải là số nguyên!',
            'is_restrict_patient_type.in'         => config('keywords')['execute_room']['is_restrict_patient_type'].' phải là 0 hoặc 1!', 
            
            'is_block_num_order.integer'    => config('keywords')['execute_room']['is_block_num_order'].' phải là số nguyên!',
            'is_block_num_order.in'         => config('keywords')['execute_room']['is_block_num_order'].' phải là 0 hoặc 1!', 

            'default_service_id.integer'     => config('keywords')['execute_room']['default_service_id'].' phải là số nguyên!',
            'default_service_id.exists'      => config('keywords')['execute_room']['default_service_id'].' = '.$this->default_service_id.' không tồn tại!',  
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
