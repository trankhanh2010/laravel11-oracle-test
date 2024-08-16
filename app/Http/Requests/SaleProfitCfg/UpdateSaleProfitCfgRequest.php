<?php

namespace App\Http\Requests\SaleProfitCfg;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateSaleProfitCfgRequest extends FormRequest
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
            'ratio' =>          'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'imp_price_from' => 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'imp_price_to' =>   'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|gt:imp_price_from',
            'is_medicine' => [
                'nullable',
                'integer',
                'in:0,1',
                function ($attribute, $value, $fail) {
                    if (($this->is_material == 1) && ($value == 1)) {
                        $fail(config('keywords')['sale_profit_cfg']['is_medicine'] . ' chỉ được chọn khi ' . config('keywords')['sale_profit_cfg']['is_material'] . ' trống!');
                    }
                },
            ],
            'is_material' => [
                'nullable',
                'integer',
                'in:0,1',
                function ($attribute, $value, $fail) {
                    if (($this->is_medicine == 1) && ($value == 1)) {
                        $fail(config('keywords')['sale_profit_cfg']['is_material'] . ' chỉ được chọn khi ' . config('keywords')['sale_profit_cfg']['is_medicine'] . ' trống!');
                    }
                },
            ],
            'is_common_medicine' => [
                'nullable',
                'integer',
                'in:0,1',
                function ($attribute, $value, $fail) {
                    if (($this->is_medicine != 1) && ($value == 1)) {
                        $fail(config('keywords')['sale_profit_cfg']['is_common_medicine'] . ' chỉ được chọn khi ' . config('keywords')['sale_profit_cfg']['is_medicine'] . ' được chọn!');
                    }
                },
            ],
            'is_functional_food' => [
                'nullable',
                'integer',
                'in:0,1',
                function ($attribute, $value, $fail) {
                    if (($this->is_medicine != 1) && ($value == 1)) {
                        $fail(config('keywords')['sale_profit_cfg']['is_functional_food'] . ' chỉ được chọn khi ' . config('keywords')['sale_profit_cfg']['is_medicine'] . ' được chọn!');
                    }
                },
            ],
            'is_drug_store' => 'nullable|integer|in:0,1',

        ];
    }
    public function messages()
    {
        return [
            'ratio.required'    => config('keywords')['sale_profit_cfg']['ratio'] . config('keywords')['error']['required'],
            'ratio.numeric'     => config('keywords')['sale_profit_cfg']['ratio'] . config('keywords')['error']['numeric'],
            'ratio.regex'       => config('keywords')['sale_profit_cfg']['ratio'] . config('keywords')['error']['regex_19_4'],
            'ratio.min'         => config('keywords')['sale_profit_cfg']['ratio'] . config('keywords')['error']['integer_min'],

            'imp_price_from.numeric'     => config('keywords')['sale_profit_cfg']['imp_price_from'] . config('keywords')['error']['numeric'],
            'imp_price_from.regex'       => config('keywords')['sale_profit_cfg']['imp_price_from'] . config('keywords')['error']['regex_19_4'],
            'imp_price_from.min'         => config('keywords')['sale_profit_cfg']['imp_price_from'] . config('keywords')['error']['integer_min'],

            'imp_price_to.numeric'     => config('keywords')['sale_profit_cfg']['imp_price_to'] . config('keywords')['error']['numeric'],
            'imp_price_to.regex'       => config('keywords')['sale_profit_cfg']['imp_price_to'] . config('keywords')['error']['regex_19_4'],
            'imp_price_to.min'         => config('keywords')['sale_profit_cfg']['imp_price_to'] . config('keywords')['error']['integer_min'],
            'imp_price_to.gt'         => config('keywords')['sale_profit_cfg']['imp_price_to'] . config('keywords')['error']['gt'],

            'is_medicine.integer'    => config('keywords')['sale_profit_cfg']['is_medicine'] . config('keywords')['error']['integer'],
            'is_medicine.in'         => config('keywords')['sale_profit_cfg']['is_medicine'] . config('keywords')['error']['in'],

            'is_material.integer'    => config('keywords')['sale_profit_cfg']['is_material'] . config('keywords')['error']['integer'],
            'is_material.in'         => config('keywords')['sale_profit_cfg']['is_material'] . config('keywords')['error']['in'],

            'is_common_medicine.integer'    => config('keywords')['sale_profit_cfg']['is_common_medicine'] . config('keywords')['error']['integer'],
            'is_common_medicine.in'         => config('keywords')['sale_profit_cfg']['is_common_medicine'] . config('keywords')['error']['in'],

            'is_functional_food.integer'    => config('keywords')['sale_profit_cfg']['is_functional_food'] . config('keywords')['error']['integer'],
            'is_functional_food.in'         => config('keywords')['sale_profit_cfg']['is_functional_food'] . config('keywords')['error']['in'],

            'is_drug_store.integer'    => config('keywords')['sale_profit_cfg']['is_drug_store'] . config('keywords')['error']['integer'],
            'is_drug_store.in'         => config('keywords')['sale_profit_cfg']['is_drug_store'] . config('keywords')['error']['in'],
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
