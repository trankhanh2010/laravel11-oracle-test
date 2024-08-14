<?php

namespace App\Http\Requests\BhytParam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateBhytParamRequest extends FormRequest
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
            'base_salary' =>                    'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'min_total_by_salary' =>            'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'max_total_package_by_salary' =>    'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'second_stent_paid_ratio' =>        'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'priority' =>                       'nullable|integer', 
            'from_time' =>                      'nullable|integer|regex:/^\d{14}$/',
            'to_time' =>                        'nullable|integer|regex:/^\d{14}$/|gte:from_time',           
        ];
    }
    public function messages()
    {
        return [
            'base_salary.required'    => config('keywords')['bhyt_param']['base_salary'].config('keywords')['error']['required'],
            'base_salary.numeric'     => config('keywords')['bhyt_param']['base_salary'].config('keywords')['error']['numeric'],
            'base_salary.regex'       => config('keywords')['bhyt_param']['base_salary'].config('keywords')['error']['regex_19_4'],
            'base_salary.max'         => config('keywords')['bhyt_param']['base_salary'].config('keywords')['error']['integer_max'],

            'min_total_by_salary.required'    => config('keywords')['bhyt_param']['min_total_by_salary'].config('keywords')['error']['required'],
            'min_total_by_salary.numeric'     => config('keywords')['bhyt_param']['min_total_by_salary'].config('keywords')['error']['numeric'],
            'min_total_by_salary.regex'       => config('keywords')['bhyt_param']['min_total_by_salary'].config('keywords')['error']['regex_19_4'],
            'min_total_by_salary.max'         => config('keywords')['bhyt_param']['min_total_by_salary'].config('keywords')['error']['integer_max'],

            'max_total_package_by_salary.required'    => config('keywords')['bhyt_param']['max_total_package_by_salary'].config('keywords')['error']['required'],
            'max_total_package_by_salary.numeric'     => config('keywords')['bhyt_param']['max_total_package_by_salary'].config('keywords')['error']['numeric'],
            'max_total_package_by_salary.regex'       => config('keywords')['bhyt_param']['max_total_package_by_salary'].config('keywords')['error']['regex_19_4'],
            'max_total_package_by_salary.max'         => config('keywords')['bhyt_param']['max_total_package_by_salary'].config('keywords')['error']['integer_max'],

            'second_stent_paid_ratio.required'    => config('keywords')['bhyt_param']['second_stent_paid_ratio'].config('keywords')['error']['required'],
            'second_stent_paid_ratio.numeric'     => config('keywords')['bhyt_param']['second_stent_paid_ratio'].config('keywords')['error']['numeric'],
            'second_stent_paid_ratio.regex'       => config('keywords')['bhyt_param']['second_stent_paid_ratio'].config('keywords')['error']['regex_19_4'],
            'second_stent_paid_ratio.max'         => config('keywords')['bhyt_param']['second_stent_paid_ratio'].config('keywords')['error']['integer_max'],

            'priority.integer'     => config('keywords')['bhyt_param']['priority'].config('keywords')['error']['integer'],

            'from_time.integer'            => config('keywords')['bhyt_param']['from_time'].config('keywords')['error']['integer'],
            'from_time.regex'              => config('keywords')['bhyt_param']['from_time'].config('keywords')['error']['regex_ymdhis'],

            'to_time.integer'            => config('keywords')['bhyt_param']['to_time'].config('keywords')['error']['integer'],
            'to_time.regex'              => config('keywords')['bhyt_param']['to_time'].config('keywords')['error']['regex_ymdhis'],
            'to_time.gte'                => config('keywords')['bhyt_param']['to_time'].config('keywords')['error']['gte'],



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
