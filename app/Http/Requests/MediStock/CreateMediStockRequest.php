<?php

namespace App\Http\Requests\MediStock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateMediStockRequest extends FormRequest
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
            'medi_stock_code' =>                'required|string|max:20|unique:App\Models\HIS\MediStock,medi_stock_code',
            'medi_stock_name' =>                'required|string|max:100',
            'department_id' =>                  'required|integer|exists:App\Models\HIS\Department,id',
            'room_type_id'  =>                  'required|integer|exists:App\Models\HIS\RoomType,id',
            'bhyt_head_code' =>                 'nullable|string|max:200',
            'not_in_bhyt_head_code' =>          'nullable|string|max:200',
            'parent_id' =>                      'nullable|integer|exists:App\Models\HIS\MediStock,id',
            'is_allow_imp_supplier' =>          'nullable|integer|in:0,1',
            'do_not_imp_medicine' =>            'nullable|integer|in:0,1',
            'do_not_imp_material' =>            'nullable|integer|in:0,1',
            'is_odd' =>                         'nullable|integer|in:0,1',
            'is_blood' =>                       'nullable|integer|in:0,1',
            'is_show_ddt' =>                    'nullable|integer|in:0,1',
            'is_planning_trans_as_default' =>   'nullable|integer|in:0,1',
            'is_auto_create_chms_imp' =>        'nullable|integer|in:0,1',
            'is_auto_create_reusable_imp' =>    'nullable|integer|in:0,1',
            'is_goods_restrict' =>              'nullable|integer|in:0,1',
            'is_show_inpatient_return_pres' =>  'nullable|integer|in:0,1',
            'is_moba_change_amount' =>          'nullable|integer|in:0,1',
            'is_for_rejected_moba' =>           'nullable|integer|in:0,1',
            'is_show_anticipate' =>             'nullable|integer|in:0,1',
            'is_cabinet' =>                     'nullable|integer|in:0,1',
            'is_new_medicine' =>                'nullable|integer|in:0,1',
            'is_traditional_medicine' =>        'nullable|integer|in:0,1',
            'is_drug_store' =>                  'nullable|integer|in:0,1',
            'is_show_drug_store' =>             'nullable|integer|in:0,1',
            'is_business' =>                    'nullable|integer|in:0,1',
            'is_expend' =>                      'nullable|integer|in:0,1',
            'patient_classify_ids' =>           'nullable|string|max:200',
            'cabinet_manage_option' =>          'nullable|integer|in:1,2,3',

        ];
    }
    public function messages()
    {
        return [
            'medi_stock_code.required'  => config('keywords')['medi_stock']['medi_stock_code'].' không được bỏ trống!',
            'medi_stock_code.string'    => config('keywords')['medi_stock']['medi_stock_code'].' phải là chuỗi string!',
            'medi_stock_code.max'       => config('keywords')['medi_stock']['medi_stock_code'].' tối đa 20 kí tự!',            
            'medi_stock_code.unique'    => config('keywords')['medi_stock']['medi_stock_code'].' = '.$this->medi_stock_code.' đã tồn tại!',

            'medi_stock_name.required'  => config('keywords')['medi_stock']['medi_stock_name'].' không được bỏ trống!',
            'medi_stock_name.string'    => config('keywords')['medi_stock']['medi_stock_name'].' phải là chuỗi string!',
            'medi_stock_name.max'       => config('keywords')['medi_stock']['medi_stock_name'].' tối đa 100 kí tự!',   
            
            'department_id.required'   => config('keywords')['medi_stock']['department_id'].' không được bỏ trống!',            
            'department_id.integer'    => config('keywords')['medi_stock']['department_id'].' phải là số nguyên!',
            'department_id.exists'     => config('keywords')['medi_stock']['department_id'].' = '.$this->department_id.' không tồn tại!',      
            
            'room_type_id.required'    => config('keywords')['medi_stock']['room_type_id'].' không được bỏ trống!',            
            'room_type_id.integer'     => config('keywords')['medi_stock']['room_type_id'].' phải là số nguyên!',
            'room_type_id.exists'      => config('keywords')['medi_stock']['room_type_id'].' = '.$this->room_type_id.' không tồn tại!',  
            
            'bhyt_head_code.string'    => config('keywords')['medi_stock']['bhyt_head_code'].' phải là chuỗi string!',
            'bhyt_head_code.max'       => config('keywords')['medi_stock']['bhyt_head_code'].' tối đa 200 kí tự!',   

            'not_in_bhyt_head_code.string'    => config('keywords')['medi_stock']['not_in_bhyt_head_code'].' phải là chuỗi string!',
            'not_in_bhyt_head_code.max'       => config('keywords')['medi_stock']['not_in_bhyt_head_code'].' tối đa 200 kí tự!', 

            'parent_id.integer'    => config('keywords')['medi_stock']['parent_id'].' phải là số nguyên!',
            'parent_id.exists'     => config('keywords')['medi_stock']['parent_id'].' = '.$this->parent_id.' không tồn tại!',    

            'is_allow_imp_supplier.integer'   => config('keywords')['medi_stock']['is_allow_imp_supplier'].' phải là số nguyên!',
            'is_allow_imp_supplier.in'        => config('keywords')['medi_stock']['is_allow_imp_supplier'].' phải là 0 hoặc 1!',  
            
            'do_not_imp_medicine.integer'   => config('keywords')['medi_stock']['do_not_imp_medicine'].' phải là số nguyên!',
            'do_not_imp_medicine.in'        => config('keywords')['medi_stock']['do_not_imp_medicine'].' phải là 0 hoặc 1!',  
            
            'do_not_imp_material.integer'   => config('keywords')['medi_stock']['do_not_imp_material'].' phải là số nguyên!',
            'do_not_imp_material.in'        => config('keywords')['medi_stock']['do_not_imp_material'].' phải là 0 hoặc 1!',  
            
            'is_odd.integer'   => config('keywords')['medi_stock']['is_odd'].' phải là số nguyên!',
            'is_odd.in'        => config('keywords')['medi_stock']['is_odd'].' phải là 0 hoặc 1!',  
            
            'is_blood.integer'   => config('keywords')['medi_stock']['is_blood'].' phải là số nguyên!',
            'is_blood.in'        => config('keywords')['medi_stock']['is_blood'].' phải là 0 hoặc 1!',  
            
            'is_show_ddt.integer'   => config('keywords')['medi_stock']['is_show_ddt'].' phải là số nguyên!',
            'is_show_ddt.in'        => config('keywords')['medi_stock']['is_show_ddt'].' phải là 0 hoặc 1!',  
            
            'is_planning_trans_as_default.integer'   => config('keywords')['medi_stock']['is_planning_trans_as_default'].' phải là số nguyên!',
            'is_planning_trans_as_default.in'        => config('keywords')['medi_stock']['is_planning_trans_as_default'].' phải là 0 hoặc 1!',  
            
            'is_auto_create_chms_imp.integer'   => config('keywords')['medi_stock']['is_auto_create_chms_imp'].' phải là số nguyên!',
            'is_auto_create_chms_imp.in'        => config('keywords')['medi_stock']['is_auto_create_chms_imp'].' phải là 0 hoặc 1!',  
            
            'is_auto_create_reusable_imp.integer'   => config('keywords')['medi_stock']['is_auto_create_reusable_imp'].' phải là số nguyên!',
            'is_auto_create_reusable_imp.in'        => config('keywords')['medi_stock']['is_auto_create_reusable_imp'].' phải là 0 hoặc 1!',  
            
            'is_goods_restrict.integer'   => config('keywords')['medi_stock']['is_goods_restrict'].' phải là số nguyên!',
            'is_goods_restrict.in'        => config('keywords')['medi_stock']['is_goods_restrict'].' phải là 0 hoặc 1!',  
            
            'is_show_inpatient_return_pres.integer'   => config('keywords')['medi_stock']['is_show_inpatient_return_pres'].' phải là số nguyên!',
            'is_show_inpatient_return_pres.in'        => config('keywords')['medi_stock']['is_show_inpatient_return_pres'].' phải là 0 hoặc 1!',  
            
            'is_moba_change_amount.integer'   => config('keywords')['medi_stock']['is_moba_change_amount'].' phải là số nguyên!',
            'is_moba_change_amount.in'        => config('keywords')['medi_stock']['is_moba_change_amount'].' phải là 0 hoặc 1!',  
            
            'is_for_rejected_moba.integer'   => config('keywords')['medi_stock']['is_for_rejected_moba'].' phải là số nguyên!',
            'is_for_rejected_moba.in'        => config('keywords')['medi_stock']['is_for_rejected_moba'].' phải là 0 hoặc 1!',  
            
            'is_show_anticipate.integer'   => config('keywords')['medi_stock']['is_show_anticipate'].' phải là số nguyên!',
            'is_show_anticipate.in'        => config('keywords')['medi_stock']['is_show_anticipate'].' phải là 0 hoặc 1!',  
            
            'is_cabinet.integer'   => config('keywords')['medi_stock']['is_cabinet'].' phải là số nguyên!',
            'is_cabinet.in'        => config('keywords')['medi_stock']['is_cabinet'].' phải là 0 hoặc 1!',  
            
            'is_new_medicine.integer'   => config('keywords')['medi_stock']['is_new_medicine'].' phải là số nguyên!',
            'is_new_medicine.in'        => config('keywords')['medi_stock']['is_new_medicine'].' phải là 0 hoặc 1!',  
            
            'is_traditional_medicine.integer'   => config('keywords')['medi_stock']['is_traditional_medicine'].' phải là số nguyên!',
            'is_traditional_medicine.in'        => config('keywords')['medi_stock']['is_traditional_medicine'].' phải là 0 hoặc 1!',  
            
            'is_drug_store.integer'   => config('keywords')['medi_stock']['is_drug_store'].' phải là số nguyên!',
            'is_drug_store.in'        => config('keywords')['medi_stock']['is_drug_store'].' phải là 0 hoặc 1!',  
            
            'is_show_drug_store.integer'   => config('keywords')['medi_stock']['is_show_drug_store'].' phải là số nguyên!',
            'is_show_drug_store.in'        => config('keywords')['medi_stock']['is_show_drug_store'].' phải là 0 hoặc 1!',  
            
            'is_business.integer'   => config('keywords')['medi_stock']['is_business'].' phải là số nguyên!',
            'is_business.in'        => config('keywords')['medi_stock']['is_business'].' phải là 0 hoặc 1!',  
            
            'is_expend.integer'   => config('keywords')['medi_stock']['is_expend'].' phải là số nguyên!',
            'is_expend.in'        => config('keywords')['medi_stock']['is_expend'].' phải là 0 hoặc 1!',  

            'cabinet_manage_option.integer'   => config('keywords')['medi_stock']['cabinet_manage_option'].' phải là số nguyên!',
            'cabinet_manage_option.in'        => config('keywords')['medi_stock']['cabinet_manage_option'].' phải là 1, 2 hoặc 3!',  

        ];
    }
    protected function prepareForValidation()
    {
        if ($this->has('patient_classify_ids')) {
            $this->merge([
                'patient_classify_ids_list' => explode(',', $this->patient_classify_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('patient_classify_ids') && (strlen($this->patient_classify_ids) >= 200)) {
                $validator->errors()->add('patient_classify_ids', config('keywords')['medi_stock']['patient_classify_ids'].' tối đa 200 kí tự!');
            }
            if ($this->has('patient_classify_ids_list') && ($this->patient_classify_ids_list[0] != null)) {
                foreach ($this->patient_classify_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientClassify::find($id)) {
                        $validator->errors()->add('patient_classify_ids', 'Phân loại bệnh nhân với id = ' . $id . ' trong danh sách phân loại bệnh nhân không tồn tại!');
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
