<?php

namespace App\Http\Requests\MediOrg;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\SDA\District;
use App\Models\SDA\Province;
class CreateMediOrgRequest extends FormRequest
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
            'medi_org_code' =>                  'required|string|max:6|unique:App\Models\HIS\MediOrg,medi_org_code',
            'medi_org_name' =>                  'required|string|max:500',
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
            'rank_code' =>                      'nullable|string|max:2',
            'level_code' =>                     'nullable|string|max:2|in:1,2,3,4'
        ];
    }
    public function messages()
    {
        return [
            'medi_org_code.required'  => config('keywords')['medi_org']['medi_org_code'].' không được bỏ trống!',
            'medi_org_code.string'    => config('keywords')['medi_org']['medi_org_code'].' phải là chuỗi string!',
            'medi_org_code.max'       => config('keywords')['medi_org']['medi_org_code'].' tối đa 6 kí tự!',            
            'medi_org_code.unique'    => config('keywords')['medi_org']['medi_org_code'].' = '.$this->medi_org_code.' đã tồn tại!',

            'medi_org_name.required'  => config('keywords')['medi_org']['medi_org_name'].' không được bỏ trống!',
            'medi_org_name.string'    => config('keywords')['medi_org']['medi_org_name'].' phải là chuỗi string!',
            'medi_org_name.max'       => config('keywords')['medi_org']['medi_org_name'].' tối đa 500 kí tự!',   
             
            'province_code.string'  => config('keywords')['medi_org']['province_code'].' phải là chuỗi string!',
            'province_code.max'     => config('keywords')['medi_org']['province_code'].' tối đa 4 kí tự!',      
            'province_code.exists'  => config('keywords')['medi_org']['province_code'].' = '.$this->province_code.' không tồn tại!', 

            'province_name.string'  => config('keywords')['medi_org']['province_name'].' phải là chuỗi string!',
            'province_name.max'     => config('keywords')['medi_org']['province_name'].' tối đa 100 kí tự!',      
            'province_name.exists'  => config('keywords')['medi_org']['province_name'].' = '.$this->province_name.' không trùng khớp với '.config('keywords')['medi_org']['province_code'].' = '. $this->province_code.'!', 

            'district_code.string'  => config('keywords')['medi_org']['district_code'].' phải là chuỗi string!',
            'district_code.max'     => config('keywords')['medi_org']['district_code'].' tối đa 4 kí tự!',      
            'district_code.exists'  => config('keywords')['medi_org']['district_code'].' = '.$this->district_code.' không tồn tại'.' hoặc không thuộc '.$this->province_name.'!', 

            'district_name.string'  => config('keywords')['medi_org']['district_name'].' phải là chuỗi string!',
            'district_name.max'     => config('keywords')['medi_org']['district_name'].' tối đa 100 kí tự!',      
            'district_name.exists'  => config('keywords')['medi_org']['district_name'].' = '.$this->district_name.' không trùng khớp với '.config('keywords')['medi_org']['district_code'].' = '. $this->district_code.' hoặc không thuộc '.$this->province_name.'!', 

            'commune_code.string'  => config('keywords')['medi_org']['commune_code'].' phải là chuỗi string!',
            'commune_code.max'     => config('keywords')['medi_org']['commune_code'].' tối đa 6 kí tự!',      
            'commune_code.exists'  => config('keywords')['medi_org']['commune_code'].' = '.$this->commune_code.' không tồn tại'.' hoặc không thuộc '.$this->district_name.'!', 

            'commune_name.string'  => config('keywords')['medi_org']['commune_name'].' phải là chuỗi string!',
            'commune_name.max'     => config('keywords')['medi_org']['commune_name'].' tối đa 100 kí tự!',      
            'commune_name.exists'  => config('keywords')['medi_org']['commune_name'].' = '.$this->commune_name.' không trùng khớp với '.config('keywords')['medi_org']['commune_code'].' = '. $this->commune_code.' hoặc không thuộc '.$this->district_name.'!', 
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
