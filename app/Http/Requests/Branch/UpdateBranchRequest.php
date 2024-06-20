<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\SDA\District;
use App\Models\SDA\Province;
class UpdateBranchRequest extends FormRequest
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
            'branch_name' =>                    'required|string|max:100',
            'hein_medi_org_code' =>             'nullable|string|max:6',
            'accept_hein_medi_org_code' =>      'nullable|string|max:4000',
            'sys_medi_org_code' =>              'nullable|string|max:2000',
            'province_code' =>                  'nullable|string|max:4|exists:App\Models\SDA\Province,province_code',
            'province_name' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:100',
                                                    Rule::exists('App\Models\SDA\Province','province_name')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('province_code', $this->province_code);
                                                    })
                                                ],
            'district_code' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:4',
                                                    Rule::exists('App\Models\SDA\District','district_code')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('province_id', Province::select('id')->where('province_code', $this->province_code)->value('id'));
                                                    })
                                                ],
            'district_name' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:100',
                                                    Rule::exists('App\Models\SDA\District','district_name')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('district_code', $this->district_code);
                                                    })
                                                    ->where(function (Builder $query) {
                                                        return $query->where('province_id', Province::select('id')->where('province_code', $this->province_code)->value('id'));
                                                    })
                                                ],            
            'commune_code' =>                   [
                                                    'nullable',
                                                    'string',
                                                    'max:6',
                                                    Rule::exists('App\Models\SDA\Commune','commune_code')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('district_id', District::select('id')->where('district_code', $this->district_code)->value('id'));
                                                    })                                                                                        
                                                ],
            'commune_name' =>                   [
                                                    'nullable',
                                                    'string',
                                                    'max:100',
                                                    Rule::exists('App\Models\SDA\Commune','commune_name')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('commune_code', $this->commune_code);
                                                    })
                                                    ->where(function (Builder $query) {
                                                        return $query->where('district_id', District::select('id')->where('district_code', $this->district_code)->value('id'));
                                                    })
                                                ],      
            'address' =>                        'nullable|string|max:500',
            'parent_organization_name' =>       'nullable|string|max:100',
            'hein_province_code' =>             'nullable|string|max:2',
            'hein_level_code' =>                'nullable|string|max:1|in:1,2,3,4',
            'do_not_allow_hein_level_code' =>   'nullable|string|max:100',
            'tax_code' =>                       'nullable|string|max:20',
            'account_number' =>                 'nullable|string|max:50',
            'phone' =>                          'nullable|string|max:20',
            'representative' =>                 'nullable|string|max:200',
            'position' =>                       'nullable|string|max:100',
            'representative_hein_code' =>       'nullable|string|max:20',
            'auth_letter_issue_date' =>         'nullable|integer',
            'auth_letter_num' =>                'nullable|string|max:50',
            'bank_info' =>                      'nullable|string|max:300',
            'the_branch_code' =>                'nullable|string|max:20',
            'director_loginname' =>             'nullable|string|max:50|exists:App\Models\HIS\Employee,loginname',
            'director_username' =>              'nullable|string|max:100|exists:App\Models\HIS\Employee,tdl_username',      
            'venture' =>                        'nullable|integer|in:1,2',
            'type' =>                           'nullable|integer|in:1,2',
            'form' =>                           'nullable|integer|in:1,2,3,4,5,6,7,8,9,10',
            'bed_approved' =>                   'nullable|integer|min:0',
            'bed_actual' =>                     'nullable|integer|min:0',
            'bed_resuscitation' =>              'nullable|integer|min:0',
            'bed_resuscitation_emg' =>          'nullable|integer|min:0',
            'is_use_branch_time' =>             'nullable|integer|in:0,1'
        ];
    }
    public function messages()
    {
        return [

            'branch_name.required'  => config('keywords')['branch']['branch_name'].' không được bỏ trống!',
            'branch_name.string'    => config('keywords')['branch']['branch_name'].' phải là chuỗi string!',
            'branch_name.max'       => config('keywords')['branch']['branch_name'].' tối đa 100 kí tự!',

            'hein_medi_org_code.string' => config('keywords')['branch']['hein_medi_org_code'].' phải là chuỗi string!',
            'hein_medi_org_code.max'    => config('keywords')['branch']['hein_medi_org_code'].' tối đa 6 kí tự!',

            'accept_hein_medi_org_code.string'  => config('keywords')['branch']['accept_hein_medi_org_code'].' phải là chuỗi string!',
            'accept_hein_medi_org_code.max'     => config('keywords')['branch']['accept_hein_medi_org_code'].' tối đa 4000 kí tự!',

            'sys_medi_org_code.string'  => config('keywords')['branch']['sys_medi_org_code'].' phải là chuỗi string!',
            'sys_medi_org_code.max'     => config('keywords')['branch']['sys_medi_org_code'].' tối đa 2000 kí tự!',

 
            'province_code.string'  => config('keywords')['branch']['province_code'].' phải là chuỗi string!',
            'province_code.max'     => config('keywords')['branch']['province_code'].' tối đa 4 kí tự!',      
            'province_code.exists'  => config('keywords')['branch']['province_code'].' = '.$this->province_code.' không tồn tại!', 

            'province_name.string'  => config('keywords')['branch']['province_name'].' phải là chuỗi string!',
            'province_name.max'     => config('keywords')['branch']['province_name'].' tối đa 100 kí tự!',      
            'province_name.exists'  => config('keywords')['branch']['province_name'].' = '.$this->province_name.' không trùng khớp với '.config('keywords')['medi_org']['province_code'].' = '. $this->province_code.'!', 

            'district_code.string'  => config('keywords')['branch']['district_code'].' phải là chuỗi string!',
            'district_code.max'     => config('keywords')['branch']['district_code'].' tối đa 4 kí tự!',      
            'district_code.exists'  => config('keywords')['branch']['district_code'].' = '.$this->district_code.' không tồn tại'.' hoặc không thuộc '.$this->province_name.'!', 

            'district_name.string'  => config('keywords')['branch']['district_name'].' phải là chuỗi string!',
            'district_name.max'     => config('keywords')['branch']['district_name'].' tối đa 100 kí tự!',      
            'district_name.exists'  => config('keywords')['branch']['district_name'].' = '.$this->district_name.' không trùng khớp với '.config('keywords')['medi_org']['district_code'].' = '. $this->district_code.' hoặc không thuộc '.$this->province_name.'!', 

            'commune_code.string'  => config('keywords')['branch']['commune_code'].' phải là chuỗi string!',
            'commune_code.max'     => config('keywords')['branch']['commune_code'].' tối đa 6 kí tự!',      
            'commune_code.exists'  => config('keywords')['branch']['commune_code'].' = '.$this->commune_code.' không tồn tại'.' hoặc không thuộc '.$this->district_name.'!', 

            'commune_name.string'  => config('keywords')['branch']['commune_name'].' phải là chuỗi string!',
            'commune_name.max'     => config('keywords')['branch']['commune_name'].' tối đa 100 kí tự!',      
            'commune_name.exists'  => config('keywords')['branch']['commune_name'].' = '.$this->commune_name.' không trùng khớp với '.config('keywords')['medi_org']['commune_code'].' = '. $this->commune_code.' hoặc không thuộc '.$this->district_name.'!', 

            'address.string'    => config('keywords')['branch']['address'].' phải là chuỗi string!',
            'address.max'       => config('keywords')['branch']['address'].' tối đa 500 kí tự!',

            'parent_organization_name.string'   => config('keywords')['branch']['parent_organization_name'].' phải là chuỗi string!',
            'parent_organization_name.max'      => config('keywords')['branch']['parent_organization_name'].' tối đa 100 kí tự!',

            'hein_province_code.string'     => config('keywords')['branch']['hein_province_code'].' phải là chuỗi string!',
            'hein_province_code.max'        => config('keywords')['branch']['hein_province_code'].' tối đa 2 kí tự!',

            'hein_level_code.string'    => config('keywords')['branch']['hein_level_code'].' phải là chuỗi string!',
            'hein_level_code.max'       => config('keywords')['branch']['hein_level_code'].' tối đa 1 kí tự!',
            'hein_level_code.in'        => config('keywords')['branch']['hein_level_code'].' phải là 1,2,3 hoặc 4!', 

            'do_not_allow_hein_level_code.string'   => config('keywords')['branch']['do_not_allow_hein_level_code'].' phải là chuỗi string!',
            'do_not_allow_hein_level_code.max'      => config('keywords')['branch']['do_not_allow_hein_level_code'].' tối đa 100 kí tự!',

            'tax_code.string'   => config('keywords')['branch']['tax_code'].' phải là chuỗi string!',
            'tax_code.max'      => config('keywords')['branch']['tax_code'].' tối đa 20 kí tự!',

            'account_number.string'   => config('keywords')['branch']['account_number'].' phải là chuỗi string!',
            'account_number.max'      => config('keywords')['branch']['account_number'].' tối đa 50 kí tự!',

            'phone.string'   => config('keywords')['branch']['phone'].' phải là chuỗi string!',
            'phone.max'      => config('keywords')['branch']['phone'].' tối đa 20 kí tự!',

            'representative.string'   => config('keywords')['branch']['representative'].' phải là chuỗi string!',
            'representative.max'      => config('keywords')['branch']['representative'].' tối đa 200 kí tự!',

            'position.string'   => config('keywords')['branch']['position'].' phải là chuỗi string!',
            'position.max'      => config('keywords')['branch']['position'].' tối đa 100 kí tự!',

            'representative_hein_code.string'   => config('keywords')['branch']['representative_hein_code'].' phải là chuỗi string!',
            'representative_hein_code.max'      => config('keywords')['branch']['representative_hein_code'].' tối đa 20 kí tự!',

            'auth_letter_issue_date.integer'   => config('keywords')['branch']['auth_letter_issue_date'].' phải là số nguyên!',

            'auth_letter_num.string'   => config('keywords')['branch']['auth_letter_num'].' phải là chuỗi string!',
            'auth_letter_num.max'      => config('keywords')['branch']['auth_letter_num'].' tối đa 50 kí tự!',

            'bank_info.string'   => config('keywords')['branch']['bank_info'].' phải là chuỗi string!',
            'bank_info.max'      => config('keywords')['branch']['bank_info'].' tối đa 300 kí tự!',

            'the_branch_code.string'   => config('keywords')['branch']['the_branch_code'].' phải là chuỗi string!',
            'the_branch_code.max'      => config('keywords')['branch']['the_branch_code'].' tối đa 20 kí tự!',

            'director_loginname.string'     => config('keywords')['branch']['director_loginname'].' phải là chuỗi string!',
            'director_loginname.max'        => config('keywords')['branch']['director_loginname'].' tối đa 50 kí tự!', 
            'director_loginname.exists'     => config('keywords')['branch']['director_loginname'].' = '.$this->director_loginname.' không tồn tại!',  

            'director_username.string'  => config('keywords')['branch']['director_username'].' phải là chuỗi string!',
            'director_username.max'     => config('keywords')['branch']['director_username'].' tối đa 100 kí tự!', 
            'director_username.exists'  => config('keywords')['branch']['director_username'].' = '.$this->director_username.' không tồn tại!',  

            'venture.integer'   => config('keywords')['branch']['venture'].' phải là số nguyên!',
            'venture.in'        => config('keywords')['branch']['venture'].' phải là 1 hoặc 2!',  

            'type.integer'   => config('keywords')['branch']['type'].' phải là số nguyên!',
            'type.in'        => config('keywords')['branch']['type'].' phải là 1 hoặc 2!',  

            'form.integer'   => config('keywords')['branch']['form'].' phải là số nguyên!',
            'form.in'        => config('keywords')['branch']['form'].' phải là 1, 2, 3, 4, 5, 6, 7, 8, 9 hoặc 10!',  

            'bed_approved.integer'     => config('keywords')['branch']['bed_approved'].' phải là số nguyên!',
            'bed_approved.min'         => config('keywords')['branch']['bed_approved'].' lớn hơn bằng 0!',

            'bed_actual.integer'     => config('keywords')['branch']['bed_actual'].' phải là số nguyên!',
            'bed_actual.min'         => config('keywords')['branch']['bed_actual'].' lớn hơn bằng 0!',

            'bed_resuscitation.integer'     => config('keywords')['branch']['bed_resuscitation'].' phải là số nguyên!',
            'bed_resuscitation.min'         => config('keywords')['branch']['bed_resuscitation'].' lớn hơn bằng 0!',

            'bed_resuscitation_emg.integer'     => config('keywords')['branch']['bed_resuscitation_emg'].' phải là số nguyên!',
            'bed_resuscitation_emg.min'         => config('keywords')['branch']['bed_resuscitation_emg'].' lớn hơn bằng 0!',

            'is_use_branch_time.integer'   => config('keywords')['branch']['is_use_branch_time'].' phải là số nguyên!',
            'is_use_branch_time.in'        => config('keywords')['branch']['is_use_branch_time'].' phải là 0 hoặc 1!',  

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
