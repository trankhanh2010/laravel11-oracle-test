<?php

namespace App\Http\Requests\DepositReq;

use App\Models\HIS\DepositReq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateDepositReqRequest extends FormRequest
{
    protected $depositReq;
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
        $this->depositReq = new DepositReq();
        return [
            'amount' =>                 'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
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

            'description.string'        => config('keywords')['deposit_req']['description'].config('keywords')['error']['string'],
            'description.max'           => config('keywords')['deposit_req']['description'].config('keywords')['error']['string_max'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->deposit_req_list_v_view;
            $exists = $this->depositReq
                ->where('id', $id)
                ->whereNull('deposit_id')
                ->exists();

            if (!$exists) {
                $validator->errors()->add('id', 'ID không tồn tại hoặc đã có giao dịch !');
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
