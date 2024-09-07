<?php

namespace App\Http\Requests\MediStock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class UpdateMediStockRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->medi_stock)){
            throw new HttpResponseException(returnIdError($this->medi_stock));
        }
        return [
            'medi_stock_code' =>                [
                                                    'required',
                                                    'string',
                                                    'max:20',
                                                    Rule::unique('App\Models\HIS\MediStock')->ignore($this->medi_stock),
                                                ],
            'medi_stock_name' =>                'required|string|max:100',
            'department_id' =>                  [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Department', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'room_type_id'  =>                  [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\RoomType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'bhyt_head_code' =>                 'nullable|string|max:200',
            'not_in_bhyt_head_code' =>          'nullable|string|max:200',
            'parent_id' =>                      [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\MediStock', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                    'not_in:'.$this->medi_stock,
                                                ],
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
            'medi_stock_exty' =>                'nullable',
            'medi_stock_imty' =>                'nullable',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'medi_stock_code.required'  => config('keywords')['medi_stock']['medi_stock_code'].config('keywords')['error']['required'],
            'medi_stock_code.string'    => config('keywords')['medi_stock']['medi_stock_code'].config('keywords')['error']['string'],
            'medi_stock_code.max'       => config('keywords')['medi_stock']['medi_stock_code'].config('keywords')['error']['string_max'],            
            'medi_stock_code.unique'    => config('keywords')['medi_stock']['medi_stock_code'].config('keywords')['error']['unique'],

            'medi_stock_name.required'  => config('keywords')['medi_stock']['medi_stock_name'].config('keywords')['error']['required'],
            'medi_stock_name.string'    => config('keywords')['medi_stock']['medi_stock_name'].config('keywords')['error']['string'],
            'medi_stock_name.max'       => config('keywords')['medi_stock']['medi_stock_name'].config('keywords')['error']['string_max'],   
            
            'department_id.required'   => config('keywords')['medi_stock']['department_id'].config('keywords')['error']['required'],            
            'department_id.integer'    => config('keywords')['medi_stock']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'     => config('keywords')['medi_stock']['department_id'].config('keywords')['error']['exists'],      
            
            'room_type_id.required'    => config('keywords')['medi_stock']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['medi_stock']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['medi_stock']['room_type_id'].config('keywords')['error']['exists'],  
            
            'bhyt_head_code.string'    => config('keywords')['medi_stock']['bhyt_head_code'].config('keywords')['error']['string'],
            'bhyt_head_code.max'       => config('keywords')['medi_stock']['bhyt_head_code'].config('keywords')['error']['string_max'],   

            'not_in_bhyt_head_code.string'    => config('keywords')['medi_stock']['not_in_bhyt_head_code'].config('keywords')['error']['string'],
            'not_in_bhyt_head_code.max'       => config('keywords')['medi_stock']['not_in_bhyt_head_code'].config('keywords')['error']['string_max'], 

            'parent_id.integer'    => config('keywords')['medi_stock']['parent_id'].config('keywords')['error']['integer'],
            'parent_id.exists'     => config('keywords')['medi_stock']['parent_id'].config('keywords')['error']['exists'],   
            'parent_id.not_in'     => config('keywords')['error']['parent_not_in_id'], 

            'is_allow_imp_supplier.integer'   => config('keywords')['medi_stock']['is_allow_imp_supplier'].config('keywords')['error']['integer'],
            'is_allow_imp_supplier.in'        => config('keywords')['medi_stock']['is_allow_imp_supplier'].config('keywords')['error']['in'],  
            
            'do_not_imp_medicine.integer'   => config('keywords')['medi_stock']['do_not_imp_medicine'].config('keywords')['error']['integer'],
            'do_not_imp_medicine.in'        => config('keywords')['medi_stock']['do_not_imp_medicine'].config('keywords')['error']['in'],  
            
            'do_not_imp_material.integer'   => config('keywords')['medi_stock']['do_not_imp_material'].config('keywords')['error']['integer'],
            'do_not_imp_material.in'        => config('keywords')['medi_stock']['do_not_imp_material'].config('keywords')['error']['in'],  
            
            'is_odd.integer'   => config('keywords')['medi_stock']['is_odd'].config('keywords')['error']['integer'],
            'is_odd.in'        => config('keywords')['medi_stock']['is_odd'].config('keywords')['error']['in'],  
            
            'is_blood.integer'   => config('keywords')['medi_stock']['is_blood'].config('keywords')['error']['integer'],
            'is_blood.in'        => config('keywords')['medi_stock']['is_blood'].config('keywords')['error']['in'],  
            
            'is_show_ddt.integer'   => config('keywords')['medi_stock']['is_show_ddt'].config('keywords')['error']['integer'],
            'is_show_ddt.in'        => config('keywords')['medi_stock']['is_show_ddt'].config('keywords')['error']['in'],  
            
            'is_planning_trans_as_default.integer'   => config('keywords')['medi_stock']['is_planning_trans_as_default'].config('keywords')['error']['integer'],
            'is_planning_trans_as_default.in'        => config('keywords')['medi_stock']['is_planning_trans_as_default'].config('keywords')['error']['in'],  
            
            'is_auto_create_chms_imp.integer'   => config('keywords')['medi_stock']['is_auto_create_chms_imp'].config('keywords')['error']['integer'],
            'is_auto_create_chms_imp.in'        => config('keywords')['medi_stock']['is_auto_create_chms_imp'].config('keywords')['error']['in'],  
            
            'is_auto_create_reusable_imp.integer'   => config('keywords')['medi_stock']['is_auto_create_reusable_imp'].config('keywords')['error']['integer'],
            'is_auto_create_reusable_imp.in'        => config('keywords')['medi_stock']['is_auto_create_reusable_imp'].config('keywords')['error']['in'],  
            
            'is_goods_restrict.integer'   => config('keywords')['medi_stock']['is_goods_restrict'].config('keywords')['error']['integer'],
            'is_goods_restrict.in'        => config('keywords')['medi_stock']['is_goods_restrict'].config('keywords')['error']['in'],  
            
            'is_show_inpatient_return_pres.integer'   => config('keywords')['medi_stock']['is_show_inpatient_return_pres'].config('keywords')['error']['integer'],
            'is_show_inpatient_return_pres.in'        => config('keywords')['medi_stock']['is_show_inpatient_return_pres'].config('keywords')['error']['in'],  
            
            'is_moba_change_amount.integer'   => config('keywords')['medi_stock']['is_moba_change_amount'].config('keywords')['error']['integer'],
            'is_moba_change_amount.in'        => config('keywords')['medi_stock']['is_moba_change_amount'].config('keywords')['error']['in'],  
            
            'is_for_rejected_moba.integer'   => config('keywords')['medi_stock']['is_for_rejected_moba'].config('keywords')['error']['integer'],
            'is_for_rejected_moba.in'        => config('keywords')['medi_stock']['is_for_rejected_moba'].config('keywords')['error']['in'],  
            
            'is_show_anticipate.integer'   => config('keywords')['medi_stock']['is_show_anticipate'].config('keywords')['error']['integer'],
            'is_show_anticipate.in'        => config('keywords')['medi_stock']['is_show_anticipate'].config('keywords')['error']['in'],  
            
            'is_cabinet.integer'   => config('keywords')['medi_stock']['is_cabinet'].config('keywords')['error']['integer'],
            'is_cabinet.in'        => config('keywords')['medi_stock']['is_cabinet'].config('keywords')['error']['in'],  
            
            'is_new_medicine.integer'   => config('keywords')['medi_stock']['is_new_medicine'].config('keywords')['error']['integer'],
            'is_new_medicine.in'        => config('keywords')['medi_stock']['is_new_medicine'].config('keywords')['error']['in'],  
            
            'is_traditional_medicine.integer'   => config('keywords')['medi_stock']['is_traditional_medicine'].config('keywords')['error']['integer'],
            'is_traditional_medicine.in'        => config('keywords')['medi_stock']['is_traditional_medicine'].config('keywords')['error']['in'],  
            
            'is_drug_store.integer'   => config('keywords')['medi_stock']['is_drug_store'].config('keywords')['error']['integer'],
            'is_drug_store.in'        => config('keywords')['medi_stock']['is_drug_store'].config('keywords')['error']['in'],  
            
            'is_show_drug_store.integer'   => config('keywords')['medi_stock']['is_show_drug_store'].config('keywords')['error']['integer'],
            'is_show_drug_store.in'        => config('keywords')['medi_stock']['is_show_drug_store'].config('keywords')['error']['in'],  
            
            'is_business.integer'   => config('keywords')['medi_stock']['is_business'].config('keywords')['error']['integer'],
            'is_business.in'        => config('keywords')['medi_stock']['is_business'].config('keywords')['error']['in'],  
            
            'is_expend.integer'   => config('keywords')['medi_stock']['is_expend'].config('keywords')['error']['integer'],
            'is_expend.in'        => config('keywords')['medi_stock']['is_expend'].config('keywords')['error']['in'],  

            'cabinet_manage_option.integer'   => config('keywords')['medi_stock']['cabinet_manage_option'].config('keywords')['error']['integer'],
            'cabinet_manage_option.in'        => config('keywords')['medi_stock']['cabinet_manage_option'].config('keywords')['error']['in'],  

            'patient_classify_ids.string'    => config('keywords')['medi_stock']['patient_classify_ids'].config('keywords')['error']['string'],
            'patient_classify_ids.max'       => config('keywords')['medi_stock']['patient_classify_ids'].config('keywords')['error']['string_max'],  

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 

        ];
    }
    protected function prepareForValidation()
    {
        if ($this->has('patient_classify_ids')) {
            $this->merge([
                'patient_classify_ids_list' => explode(',', $this->patient_classify_ids),
            ]);
        }
        if ($this->has('medi_stock_exty')) {
            $this->merge([
                'medi_stock_exty' => json_decode($this->medi_stock_exty),
            ]);
        }
        if ($this->has('medi_stock_imty')) {
            $this->merge([
                'medi_stock_imty' => json_decode($this->medi_stock_imty),
            ]);
        }
        
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('patient_classify_ids_list') && ($this->patient_classify_ids_list[0] != null)) {
                foreach ($this->patient_classify_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientClassify::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('patient_classify_ids', 'Phân loại bệnh nhân với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            //////////
            if ($this->has('medi_stock_exty') && (!is_array($this->medi_stock_exty))) {
                $validator->errors()->add('medi_stock_exty', config('keywords')['medi_stock']['medi_stock_exty'].' phải là chuỗi json hợp lệ và có thể được chuyển sang mảng!');
            }
            if ($this->has('medi_stock_exty') && ($this->medi_stock_exty[0] != null)) {
                foreach ($this->medi_stock_exty as $item) {
                    if (!is_numeric($item->id) || !\App\Models\HIS\ExpMestType::where('id', $item->id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medi_stock_exty', 'Bản ghi với id = ' . $item->id . ' trong '.config('keywords')['medi_stock']['medi_stock_exty'].' không tồn tại!'. config('keywords')['error']['not_active']);
                    }
                    if(!is_numeric($item->is_auto_approve) || (!in_array($item->is_auto_approve, [0, 1]))){
                        $validator->errors()->add('medi_stock_exty', config('keywords')['medi_stock']['is_auto_approve'].' trong '.config('keywords')['medi_stock']['medi_stock_exty'] .' phải là số nguyên và có giá trị 0 hoặc 1!');
                    }
                    if(!is_numeric($item->is_auto_execute) || (!in_array($item->is_auto_execute, [0, 1]))){
                        $validator->errors()->add('medi_stock_exty', config('keywords')['medi_stock']['is_auto_execute'].' trong '.config('keywords')['medi_stock']['medi_stock_exty'] .'phải là số nguyên và có giá trị 0 hoặc 1!');
                    }
                }
            }
            /////////
            if ($this->has('medi_stock_imty') && (!is_array($this->medi_stock_imty))) {
                $validator->errors()->add('medi_stock_imty', config('keywords')['medi_stock']['medi_stock_imty'].' phải là chuỗi json hợp lệ và có thể được chuyển sang mảng!');
            }
            if ($this->has('medi_stock_imty') && ($this->medi_stock_imty[0] != null)) {
                foreach ($this->medi_stock_imty as $item) {
                    if (!is_numeric($item->id) || !\App\Models\HIS\ImpMestType::where('id', $item->id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medi_stock_imty', 'Bản ghi với id = ' . $item->id . ' trong '.config('keywords')['medi_stock']['medi_stock_imty'].' không tồn tại!'. config('keywords')['error']['not_active']);
                    }
                    if(!is_numeric($item->is_auto_approve) || (!in_array($item->is_auto_approve, [0, 1]))){
                        $validator->errors()->add('medi_stock_imty', config('keywords')['medi_stock']['is_auto_approve'].' trong '.config('keywords')['medi_stock']['medi_stock_imty'] .'phải là số nguyên và có giá trị 0 hoặc 1!');
                    }
                    if(!is_numeric($item->is_auto_execute) || (!in_array($item->is_auto_execute, [0, 1]))){
                        $validator->errors()->add('medi_stock_imty', config('keywords')['medi_stock']['is_auto_execute'].' trong '.config('keywords')['medi_stock']['medi_stock_imty'] .'phải là số nguyên và có giá trị 0 hoặc 1!');
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
