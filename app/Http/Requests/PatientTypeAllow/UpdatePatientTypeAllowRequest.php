<?php

namespace App\Http\Requests\PatientTypeAllow;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdatePatientTypeAllowRequest extends FormRequest
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
         if(!is_numeric($this->patient_type_allow)){
            throw new HttpResponseException(returnIdError($this->patient_type_allow));
        }
        return [
            'patient_type_id' =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\PatientType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
                Rule::unique('App\Models\HIS\PatientTypeAllow')->ignore($this->patient_type_allow)
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("patient_type_id"), $this->patient_type_id)
                            ->where(DB::connection('oracle_his')->raw("patient_type_allow_id"), $this->patient_type_allow_id);
                    }),
            ],
            'patient_type_allow_id' =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\PatientType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
                Rule::unique('App\Models\HIS\PatientTypeAllow')->ignore($this->patient_type_allow)
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("patient_type_id"), $this->patient_type_id)
                            ->where(DB::connection('oracle_his')->raw("patient_type_allow_id"), $this->patient_type_allow_id);
                    }),
            ],
            'is_active' =>      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'patient_type_id.required'    => config('keywords')['patient_type_allow']['patient_type_id'] . config('keywords')['error']['required'],
            'patient_type_id.integer'     => config('keywords')['patient_type_allow']['patient_type_id'] . config('keywords')['error']['integer'],
            'patient_type_id.exists'      => config('keywords')['patient_type_allow']['patient_type_id'] . config('keywords')['error']['exists'],
            'patient_type_id.unique'      => 'Đã tồn tại cặp ' . config('keywords')['patient_type_allow']['patient_type_id'] . ' và ' . config('keywords')['patient_type_allow']['patient_type_allow_id'],

            'patient_type_allow_id.required'    => config('keywords')['patient_type_allow']['patient_type_allow_id'] . config('keywords')['error']['required'],
            'patient_type_allow_id.integer'     => config('keywords')['patient_type_allow']['patient_type_allow_id'] . config('keywords')['error']['integer'],
            'patient_type_allow_id.exists'      => config('keywords')['patient_type_allow']['patient_type_allow_id'] . config('keywords')['error']['exists'],
            'patient_type_allow_id.unique'      => 'Đã tồn tại cặp ' . config('keywords')['patient_type_allow']['patient_type_id'] . ' và ' . config('keywords')['patient_type_allow']['patient_type_allow_id'],

            'is_active.required'    => config('keywords')['all']['is_active'] . config('keywords')['error']['required'],
            'is_active.integer'     => config('keywords')['all']['is_active'] . config('keywords')['error']['integer'],
            'is_active.in'          => config('keywords')['all']['is_active'] . config('keywords')['error']['in'],
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
