<?php

namespace App\Http\Requests\MedicinePaty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateMedicinePatyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->medicine_paty)){
            throw new HttpResponseException(returnIdError($this->medicine_paty));
        }
        return [

            'medicine_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\Medicine', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                    Rule::unique('App\Models\HIS\MedicinePaty')->where(function ($query)  {
                                        return $query->where('MEDICINE_ID', intval($this->medicine_id))
                                                     ->where('PATIENT_TYPE_ID', intval($this->patient_type_id));
                                    }),
                                ], 
            'patient_type_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\PatientType', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                    Rule::unique('App\Models\HIS\MedicinePaty')->where(function ($query)  {
                                        return $query->where('MEDICINE_ID', intval($this->medicine_id))
                                                     ->where('PATIENT_TYPE_ID', intval($this->patient_type_id));
                                    }),
                                ], 
            'exp_price' =>      'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'exp_vat_ratio' =>  'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|max:1',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'medicine_id.required'    => config('keywords')['medicine_paty']['medicine_id'].config('keywords')['error']['required'],
            'medicine_id.integer'     => config('keywords')['medicine_paty']['medicine_id'].config('keywords')['error']['integer'],
            'medicine_id.exists'      => config('keywords')['medicine_paty']['medicine_id'].config('keywords')['error']['exists'],
            'medicine_id.unique'      => 'Cặp '.config('keywords')['medicine_paty']['medicine_id'].' và '.config('keywords')['medicine_paty']['patient_type_id']. ' đã tồn tại!',

            'patient_type_id.required'    => config('keywords')['medicine_paty']['patient_type_id'].config('keywords')['error']['required'],
            'patient_type_id.integer'     => config('keywords')['medicine_paty']['patient_type_id'].config('keywords')['error']['integer'],
            'patient_type_id.exists'      => config('keywords')['medicine_paty']['patient_type_id'].config('keywords')['error']['exists'],
            'patient_type_id.unique'      => 'Cặp '.config('keywords')['medicine_paty']['patient_type_id'].' và '.config('keywords')['medicine_paty']['medicine_id']. ' đã tồn tại!',

            'exp_price.required'    => config('keywords')['medicine_paty']['exp_price'].config('keywords')['error']['required'],
            'exp_price.numeric'     => config('keywords')['medicine_paty']['exp_price'].config('keywords')['error']['numeric'],
            'exp_price.regex'       => config('keywords')['medicine_paty']['exp_price'].config('keywords')['error']['regex_19_4'],
            'exp_price.min'         => config('keywords')['medicine_paty']['exp_price'].config('keywords')['error']['integer_min'],

            'exp_vat_ratio.required'    => config('keywords')['medicine_paty']['exp_vat_ratio'].config('keywords')['error']['required'],
            'exp_vat_ratio.numeric'     => config('keywords')['medicine_paty']['exp_vat_ratio'].config('keywords')['error']['numeric'],
            'exp_vat_ratio.regex'       => config('keywords')['medicine_paty']['exp_vat_ratio'].config('keywords')['error']['regex_19_4'],
            'exp_vat_ratio.min'         => config('keywords')['medicine_paty']['exp_vat_ratio'].config('keywords')['error']['integer_min'],
            'exp_vat_ratio.max'         => config('keywords')['medicine_paty']['exp_vat_ratio'].config('keywords')['error']['integer_max'],

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
