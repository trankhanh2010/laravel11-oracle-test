<?php

namespace App\Http\Requests\MedicalContract;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateMedicalContractRequest extends FormRequest
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
        return [
            'medical_contract_code' =>      'required|string|max:50|unique:App\Models\HIS\MedicalContract,medical_contract_code',
            'medical_contract_name' =>      'required|string|max:200',
            'supplier_id' =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Supplier', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'document_supplier_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Supplier', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'bid_id'   =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Bid', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],      
            'venture_agreening' => 'nullable|string|max:500',     
            'valid_from_date'  => 'nullable|integer|regex:/^\d{14}$/',
            'valid_to_date' =>  'nullable|integer|regex:/^\d{14}$/',
            'note' => 'nullable|string|max:4000',  
            
            
        ];
    }
    public function messages()
    {
        return [
            'medical_contract_code.required'    => config('keywords')['medical_contract']['medical_contract_code'].config('keywords')['error']['required'],
            'medical_contract_code.string'      => config('keywords')['medical_contract']['medical_contract_code'].config('keywords')['error']['string'],
            'medical_contract_code.max'         => config('keywords')['medical_contract']['medical_contract_code'].config('keywords')['error']['string_max'],
            'medical_contract_code.unique'      => config('keywords')['medical_contract']['medical_contract_code'].config('keywords')['error']['unique'],

            'medical_contract_name.string'      => config('keywords')['medical_contract']['medical_contract_name'].config('keywords')['error']['string'],
            'medical_contract_name.max'         => config('keywords')['medical_contract']['medical_contract_name'].config('keywords')['error']['string_max'],
            'medical_contract_name.unique'      => config('keywords')['medical_contract']['medical_contract_name'].config('keywords')['error']['unique'],

            'supplier_id.required'    => config('keywords')['medical_contract']['supplier_id'].config('keywords')['error']['required'],
            'supplier_id.integer'     => config('keywords')['medical_contract']['supplier_id'].config('keywords')['error']['integer'],
            'supplier_id.exists'      => config('keywords')['medical_contract']['supplier_id'].config('keywords')['error']['exists'],

            'document_supplier_id.integer'     => config('keywords')['medical_contract']['document_supplier_id'].config('keywords')['error']['integer'],
            'document_supplier_id.exists'      => config('keywords')['medical_contract']['document_supplier_id'].config('keywords')['error']['exists'],

            'bid_id.integer'     => config('keywords')['medical_contract']['bid_id'].config('keywords')['error']['integer'],
            'bid_id.exists'      => config('keywords')['medical_contract']['bid_id'].config('keywords')['error']['exists'],

            'venture_agreening.string'      => config('keywords')['medical_contract']['venture_agreening'].config('keywords')['error']['string'],
            'venture_agreening.max'         => config('keywords')['medical_contract']['venture_agreening'].config('keywords')['error']['string_max'],

            'valid_from_date.integer'            => config('keywords')['medical_contract']['valid_from_date'].config('keywords')['error']['integer'],
            'valid_from_date.regex'              => config('keywords')['medical_contract']['valid_from_date'].config('keywords')['error']['regex_ymdhis'],
            
            'valid_to_date.integer'            => config('keywords')['medical_contract']['valid_to_date'].config('keywords')['error']['integer'],
            'valid_to_date.regex'              => config('keywords')['medical_contract']['valid_to_date'].config('keywords')['error']['regex_ymdhis'],

            'note.string'      => config('keywords')['medical_contract']['note'].config('keywords')['error']['string'],
            'note.max'         => config('keywords')['medical_contract']['note'].config('keywords')['error']['string_max'],
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
