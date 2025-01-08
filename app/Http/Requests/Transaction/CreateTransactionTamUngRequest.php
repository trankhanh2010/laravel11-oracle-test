<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateTransactionTamUngRequest extends FormRequest
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
            'amount' =>                         'required|integer|min:0',  
            'account_book_id' => [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\AccountBook', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'pay_form_id' => [
                                'required',
                                'integer',
                                Rule::exists('App\Models\HIS\PayForm', 'id')
                                ->where(function ($query) {
                                    $query = $query
                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                }),
                            ], 
            'cashier_room_id' => [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\CashierRoom', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'treatment_id' => [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\Treatment', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
        ];
    }
    public function messages()
    {
        return [
            // 'transaction_tam_ung_code.required'    => config('keywords')['transaction_tam_ung']['transaction_tam_ung_code'].config('keywords')['error']['required'],
            // 'transaction_tam_ung_code.string'      => config('keywords')['transaction_tam_ung']['transaction_tam_ung_code'].config('keywords')['error']['string'],
            // 'transaction_tam_ung_code.max'         => config('keywords')['transaction_tam_ung']['transaction_tam_ung_code'].config('keywords')['error']['string_max'],
            // 'transaction_tam_ung_code.unique'      => config('keywords')['transaction_tam_ung']['transaction_tam_ung_code'].config('keywords')['error']['unique'],

            // 'transaction_tam_ung_name.required'    => config('keywords')['transaction_tam_ung']['transaction_tam_ung_name'].config('keywords')['error']['required'],
            // 'transaction_tam_ung_name.string'      => config('keywords')['transaction_tam_ung']['transaction_tam_ung_name'].config('keywords')['error']['string'],
            // 'transaction_tam_ung_name.max'         => config('keywords')['transaction_tam_ung']['transaction_tam_ung_name'].config('keywords')['error']['string_max'],
            // 'transaction_tam_ung_name.unique'      => config('keywords')['transaction_tam_ung']['transaction_tam_ung_name'].config('keywords')['error']['unique'],

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
