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
            'is_use_branch_time' =>             'nullable|integer|in:0,1',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [

            'branch_name.required'  => config('keywords')['branch']['branch_name'].config('keywords')['error']['required'],
            'branch_name.string'    => config('keywords')['branch']['branch_name'].config('keywords')['error']['string'],
            'branch_name.max'       => config('keywords')['branch']['branch_name'].config('keywords')['error']['string_max'],

            'hein_medi_org_code.string' => config('keywords')['branch']['hein_medi_org_code'].config('keywords')['error']['string'],
            'hein_medi_org_code.max'    => config('keywords')['branch']['hein_medi_org_code'].config('keywords')['error']['string_max'],

            'accept_hein_medi_org_code.string'  => config('keywords')['branch']['accept_hein_medi_org_code'].config('keywords')['error']['string'],
            'accept_hein_medi_org_code.max'     => config('keywords')['branch']['accept_hein_medi_org_code'].config('keywords')['error']['string_max'],

            'sys_medi_org_code.string'  => config('keywords')['branch']['sys_medi_org_code'].config('keywords')['error']['string'],
            'sys_medi_org_code.max'     => config('keywords')['branch']['sys_medi_org_code'].config('keywords')['error']['string_max'],

 
            'province_code.string'  => config('keywords')['branch']['province_code'].config('keywords')['error']['string'],
            'province_code.max'     => config('keywords')['branch']['province_code'].config('keywords')['error']['string_max'],      
            'province_code.exists'  => config('keywords')['branch']['province_code'].' = '.$this->province_code.' không tồn tại!', 

            'province_name.string'  => config('keywords')['branch']['province_name'].config('keywords')['error']['string'],
            'province_name.max'     => config('keywords')['branch']['province_name'].config('keywords')['error']['string_max'],      
            'province_name.exists'  => config('keywords')['branch']['province_name'].' = '.$this->province_name.' không trùng khớp với '.config('keywords')['medi_org']['province_code'].' = '. $this->province_code.'!', 

            'district_code.string'  => config('keywords')['branch']['district_code'].config('keywords')['error']['string'],
            'district_code.max'     => config('keywords')['branch']['district_code'].config('keywords')['error']['string_max'],      
            'district_code.exists'  => config('keywords')['branch']['district_code'].' = '.$this->district_code.' không tồn tại'.' hoặc không thuộc '.$this->province_name.'!', 

            'district_name.string'  => config('keywords')['branch']['district_name'].config('keywords')['error']['string'],
            'district_name.max'     => config('keywords')['branch']['district_name'].config('keywords')['error']['string_max'],      
            'district_name.exists'  => config('keywords')['branch']['district_name'].' = '.$this->district_name.' không trùng khớp với '.config('keywords')['medi_org']['district_code'].' = '. $this->district_code.' hoặc không thuộc '.$this->province_name.'!', 

            'commune_code.string'  => config('keywords')['branch']['commune_code'].config('keywords')['error']['string'],
            'commune_code.max'     => config('keywords')['branch']['commune_code'].config('keywords')['error']['string_max'],      
            'commune_code.exists'  => config('keywords')['branch']['commune_code'].' = '.$this->commune_code.' không tồn tại'.' hoặc không thuộc '.$this->district_name.'!', 

            'commune_name.string'  => config('keywords')['branch']['commune_name'].config('keywords')['error']['string'],
            'commune_name.max'     => config('keywords')['branch']['commune_name'].config('keywords')['error']['string_max'],      
            'commune_name.exists'  => config('keywords')['branch']['commune_name'].' = '.$this->commune_name.' không trùng khớp với '.config('keywords')['medi_org']['commune_code'].' = '. $this->commune_code.' hoặc không thuộc '.$this->district_name.'!', 

            'address.string'    => config('keywords')['branch']['address'].config('keywords')['error']['string'],
            'address.max'       => config('keywords')['branch']['address'].config('keywords')['error']['string_max'],

            'parent_organization_name.string'   => config('keywords')['branch']['parent_organization_name'].config('keywords')['error']['string'],
            'parent_organization_name.max'      => config('keywords')['branch']['parent_organization_name'].config('keywords')['error']['string_max'],

            'hein_province_code.string'     => config('keywords')['branch']['hein_province_code'].config('keywords')['error']['string'],
            'hein_province_code.max'        => config('keywords')['branch']['hein_province_code'].config('keywords')['error']['string_max'],

            'hein_level_code.string'    => config('keywords')['branch']['hein_level_code'].config('keywords')['error']['string'],
            'hein_level_code.max'       => config('keywords')['branch']['hein_level_code'].config('keywords')['error']['string_max'],
            'hein_level_code.in'        => config('keywords')['branch']['hein_level_code'].config('keywords')['error']['in'], 

            'do_not_allow_hein_level_code.string'   => config('keywords')['branch']['do_not_allow_hein_level_code'].config('keywords')['error']['string'],
            'do_not_allow_hein_level_code.max'      => config('keywords')['branch']['do_not_allow_hein_level_code'].config('keywords')['error']['string_max'],

            'tax_code.string'   => config('keywords')['branch']['tax_code'].config('keywords')['error']['string'],
            'tax_code.max'      => config('keywords')['branch']['tax_code'].config('keywords')['error']['string_max'],

            'account_number.string'   => config('keywords')['branch']['account_number'].config('keywords')['error']['string'],
            'account_number.max'      => config('keywords')['branch']['account_number'].config('keywords')['error']['string_max'],

            'phone.string'   => config('keywords')['branch']['phone'].config('keywords')['error']['string'],
            'phone.max'      => config('keywords')['branch']['phone'].config('keywords')['error']['string_max'],

            'representative.string'   => config('keywords')['branch']['representative'].config('keywords')['error']['string'],
            'representative.max'      => config('keywords')['branch']['representative'].config('keywords')['error']['string_max'],

            'position.string'   => config('keywords')['branch']['position'].config('keywords')['error']['string'],
            'position.max'      => config('keywords')['branch']['position'].config('keywords')['error']['string_max'],

            'representative_hein_code.string'   => config('keywords')['branch']['representative_hein_code'].config('keywords')['error']['string'],
            'representative_hein_code.max'      => config('keywords')['branch']['representative_hein_code'].config('keywords')['error']['string_max'],

            'auth_letter_issue_date.integer'   => config('keywords')['branch']['auth_letter_issue_date'].config('keywords')['error']['integer'],

            'auth_letter_num.string'   => config('keywords')['branch']['auth_letter_num'].config('keywords')['error']['string'],
            'auth_letter_num.max'      => config('keywords')['branch']['auth_letter_num'].config('keywords')['error']['string_max'],

            'bank_info.string'   => config('keywords')['branch']['bank_info'].config('keywords')['error']['string'],
            'bank_info.max'      => config('keywords')['branch']['bank_info'].config('keywords')['error']['string_max'],

            'the_branch_code.string'   => config('keywords')['branch']['the_branch_code'].config('keywords')['error']['string'],
            'the_branch_code.max'      => config('keywords')['branch']['the_branch_code'].config('keywords')['error']['string_max'],

            'director_loginname.string'     => config('keywords')['branch']['director_loginname'].config('keywords')['error']['string'],
            'director_loginname.max'        => config('keywords')['branch']['director_loginname'].config('keywords')['error']['string_max'], 
            'director_loginname.exists'     => config('keywords')['branch']['director_loginname'].config('keywords')['error']['exists'],  

            'director_username.string'  => config('keywords')['branch']['director_username'].config('keywords')['error']['string'],
            'director_username.max'     => config('keywords')['branch']['director_username'].config('keywords')['error']['string_max'], 
            'director_username.exists'  => config('keywords')['branch']['director_username'].config('keywords')['error']['exists'],  

            'venture.integer'   => config('keywords')['branch']['venture'].config('keywords')['error']['integer'],
            'venture.in'        => config('keywords')['branch']['venture'].config('keywords')['error']['in'],  

            'type.integer'   => config('keywords')['branch']['type'].config('keywords')['error']['integer'],
            'type.in'        => config('keywords')['branch']['type'].config('keywords')['error']['in'],  

            'form.integer'   => config('keywords')['branch']['form'].config('keywords')['error']['integer'],
            'form.in'        => config('keywords')['branch']['form'].config('keywords')['error']['in'],  

            'bed_approved.integer'     => config('keywords')['branch']['bed_approved'].config('keywords')['error']['integer'],
            'bed_approved.min'         => config('keywords')['branch']['bed_approved'].config('keywords')['error']['integer_min'],

            'bed_actual.integer'     => config('keywords')['branch']['bed_actual'].config('keywords')['error']['integer'],
            'bed_actual.min'         => config('keywords')['branch']['bed_actual'].config('keywords')['error']['integer_min'],

            'bed_resuscitation.integer'     => config('keywords')['branch']['bed_resuscitation'].config('keywords')['error']['integer'],
            'bed_resuscitation.min'         => config('keywords')['branch']['bed_resuscitation'].config('keywords')['error']['integer_min'],

            'bed_resuscitation_emg.integer'     => config('keywords')['branch']['bed_resuscitation_emg'].config('keywords')['error']['integer'],
            'bed_resuscitation_emg.min'         => config('keywords')['branch']['bed_resuscitation_emg'].config('keywords')['error']['integer_min'],

            'is_use_branch_time.integer'   => config('keywords')['branch']['is_use_branch_time'].config('keywords')['error']['integer'],
            'is_use_branch_time.in'        => config('keywords')['branch']['is_use_branch_time'].config('keywords')['error']['in'],  

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
