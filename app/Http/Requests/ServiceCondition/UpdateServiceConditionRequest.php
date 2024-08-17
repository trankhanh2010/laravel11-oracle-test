<?php

namespace App\Http\Requests\ServiceCondition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateServiceConditionRequest extends FormRequest
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
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'service_condition_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('App\Models\HIS\ServiceCondition')->ignore($this->id),
            ],
            'service_condition_name' =>      'required|string|max:1000',
            'hein_ratio' =>            'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|max:1', 
            'hein_price' =>            'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'service_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'service_condition_code.required'    => config('keywords')['service_condition']['service_condition_code'].config('keywords')['error']['required'],
            'service_condition_code.string'      => config('keywords')['service_condition']['service_condition_code'].config('keywords')['error']['string'],
            'service_condition_code.max'         => config('keywords')['service_condition']['service_condition_code'].config('keywords')['error']['string_max'],
            'service_condition_code.unique'      => config('keywords')['service_condition']['service_condition_code'].config('keywords')['error']['unique'],

            'service_condition_name.required'    => config('keywords')['service_condition']['service_condition_name'].config('keywords')['error']['required'],
            'service_condition_name.string'      => config('keywords')['service_condition']['service_condition_name'].config('keywords')['error']['string'],
            'service_condition_name.max'         => config('keywords')['service_condition']['service_condition_name'].config('keywords')['error']['string_max'],

            'hein_ratio.numeric'     => config('keywords')['service_condition']['hein_ratio'].config('keywords')['error']['numeric'],
            'hein_ratio.regex'       => config('keywords')['service_condition']['hein_ratio'].config('keywords')['error']['regex_19_4'],
            'hein_ratio.min'         => config('keywords')['service_condition']['hein_ratio'].config('keywords')['error']['integer_min'],
            'hein_ratio.max'         => config('keywords')['service_condition']['hein_ratio'].config('keywords')['error']['integer_max'],

            'hein_price.numeric'     => config('keywords')['service_condition']['hein_price'].config('keywords')['error']['numeric'],
            'hein_price.regex'       => config('keywords')['service_condition']['hein_price'].config('keywords')['error']['regex_19_4'],
            'hein_price.min'         => config('keywords')['service_condition']['hein_price'].config('keywords')['error']['integer_min'],

            'service_id.integer'     => config('keywords')['service_condition']['service_id'].config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['service_condition']['service_id'].config('keywords')['error']['exists'],

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
