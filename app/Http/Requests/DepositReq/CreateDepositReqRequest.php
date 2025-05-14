<?php

namespace App\Http\Requests\DepositReq;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CreateDepositReqRequest extends FormRequest
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
            'amount' =>                 'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'treatment_id' => [
                'required',
                'integer',
                Rule::exists('App\Models\View\TreatmentFeeListVView', 'id') 
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1)  // Lọc chưa khóa viện phí
;
                    }),
            ],
            'description' =>        'nullable|string|max:500',  
        ];
    }
    public function messages()
    {
        return [
            'amount.required'       => config('keywords')['deposit_req']['amount'].config('keywords')['error']['required'],
            'amount.numeric'        => config('keywords')['deposit_req']['amount'].config('keywords')['error']['numeric'],
            'amount.regex'          => config('keywords')['deposit_req']['amount'].config('keywords')['error']['regex_19_4'],
            'amount.min'            => config('keywords')['deposit_req']['amount'].config('keywords')['error']['integer_min'],

            'treatment_id.required'      => config('keywords')['deposit_req']['treatment_id'].config('keywords')['error']['required'],
            'treatment_id.integer'       => config('keywords')['deposit_req']['treatment_id'].config('keywords')['error']['integer'],
            'treatment_id.exists'        => config('keywords')['deposit_req']['treatment_id'].' không tồn tại hoặc đang bị khóa viện phí!', 

            'description.string'        => config('keywords')['deposit_req']['description'].config('keywords')['error']['string'],
            'description.max'           => config('keywords')['deposit_req']['description'].config('keywords')['error']['string_max'],
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
