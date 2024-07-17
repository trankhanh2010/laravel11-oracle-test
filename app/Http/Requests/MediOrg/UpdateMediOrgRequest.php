<?php

namespace App\Http\Requests\MediOrg;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\SDA\District;
use App\Models\SDA\Province;
use Illuminate\Support\Facades\DB;

class UpdateMediOrgRequest extends FormRequest
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
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'medi_org_code' =>                  [
                                                    'required',
                                                    'string',
                                                    'max:6',
                                                    Rule::unique('App\Models\HIS\MediOrg', 'medi_org_code')->ignore($this->id)
                                                ],
            'medi_org_name' =>                  'required|string|max:500',
            'province_code' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:4',
                                                    Rule::exists('App\Models\SDA\Province', 'province_code')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ], 
            'province_name' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:100',
                                                    Rule::exists('App\Models\SDA\Province','province_name')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('province_code', $this->province_code)
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    })
                                                ],
            'district_code' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:4',
                                                    Rule::exists('App\Models\SDA\District','district_code')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('province_id', Province::select('id')->where('province_code', $this->province_code)->value('id'))
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    })
                                                ],
            'district_name' =>                  [
                                                    'nullable',
                                                    'string',
                                                    'max:100',
                                                    Rule::exists('App\Models\SDA\District','district_name')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('district_code', $this->district_code)
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
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
                                                        return $query->where('district_id', District::select('id')->where('district_code', $this->district_code)->value('id'))
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    })                                                                                        
                                                ],
            'commune_name' =>                   [
                                                    'nullable',
                                                    'string',
                                                    'max:100',
                                                    Rule::exists('App\Models\SDA\Commune','commune_name')
                                                    ->where(function (Builder $query) {
                                                        return $query->where('commune_code', $this->commune_code)
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    })
                                                    ->where(function (Builder $query) {
                                                        return $query->where('district_id', District::select('id')->where('district_code', $this->district_code)->value('id'));
                                                    })
                                                ],       
            'address' =>                        'nullable|string|max:500',
            'rank_code' =>                      'nullable|string|max:2',
            'level_code' =>                     'nullable|string|max:2|in:1,2,3,4',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'medi_org_code.required'  => config('keywords')['medi_org']['medi_org_code'].config('keywords')['error']['required'],
            'medi_org_code.string'    => config('keywords')['medi_org']['medi_org_code'].config('keywords')['error']['string'],
            'medi_org_code.max'       => config('keywords')['medi_org']['medi_org_code'].config('keywords')['error']['string_max'],            
            'medi_org_code.unique'    => config('keywords')['medi_org']['medi_org_code'].config('keywords')['error']['unique'],

            'medi_org_name.required'  => config('keywords')['medi_org']['medi_org_name'].config('keywords')['error']['required'],
            'medi_org_name.string'    => config('keywords')['medi_org']['medi_org_name'].config('keywords')['error']['string'],
            'medi_org_name.max'       => config('keywords')['medi_org']['medi_org_name'].config('keywords')['error']['string_max'],   
             
            'province_code.string'  => config('keywords')['medi_org']['province_code'].config('keywords')['error']['string'],
            'province_code.max'     => config('keywords')['medi_org']['province_code'].config('keywords')['error']['string_max'],      
            'province_code.exists'  => config('keywords')['medi_org']['province_code'].' = '.$this->province_code.' không tồn tại!'.config('keywords')['error']['not_active'], 

            'province_name.string'  => config('keywords')['medi_org']['province_name'].config('keywords')['error']['string'],
            'province_name.max'     => config('keywords')['medi_org']['province_name'].config('keywords')['error']['string_max'],      
            'province_name.exists'  => config('keywords')['medi_org']['province_name'].' = '.$this->province_name.' không trùng khớp với '.config('keywords')['medi_org']['province_code'].' = '. $this->province_code.'!'.config('keywords')['error']['not_active'], 

            'district_code.string'  => config('keywords')['medi_org']['district_code'].config('keywords')['error']['string'],
            'district_code.max'     => config('keywords')['medi_org']['district_code'].config('keywords')['error']['string_max'],      
            'district_code.exists'  => config('keywords')['medi_org']['district_code'].' = '.$this->district_code.' không tồn tại'.' hoặc không thuộc '.$this->province_name.'!'.config('keywords')['error']['not_active'], 

            'district_name.string'  => config('keywords')['medi_org']['district_name'].config('keywords')['error']['string'],
            'district_name.max'     => config('keywords')['medi_org']['district_name'].config('keywords')['error']['string_max'],      
            'district_name.exists'  => config('keywords')['medi_org']['district_name'].' = '.$this->district_name.' không trùng khớp với '.config('keywords')['medi_org']['district_code'].' = '. $this->district_code.' hoặc không thuộc '.$this->province_name.'!'.config('keywords')['error']['not_active'], 

            'commune_code.string'  => config('keywords')['medi_org']['commune_code'].config('keywords')['error']['string'],
            'commune_code.max'     => config('keywords')['medi_org']['commune_code'].config('keywords')['error']['string_max'],      
            'commune_code.exists'  => config('keywords')['medi_org']['commune_code'].' = '.$this->commune_code.' không tồn tại'.' hoặc không thuộc '.$this->district_name.'!'.config('keywords')['error']['not_active'], 

            'commune_name.string'  => config('keywords')['medi_org']['commune_name'].config('keywords')['error']['string'],
            'commune_name.max'     => config('keywords')['medi_org']['commune_name'].config('keywords')['error']['string_max'],      
            'commune_name.exists'  => config('keywords')['medi_org']['commune_name'].' = '.$this->commune_name.' không trùng khớp với '.config('keywords')['medi_org']['commune_code'].' = '. $this->commune_code.' hoặc không thuộc '.$this->district_name.'!'.config('keywords')['error']['not_active'], 
        
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
