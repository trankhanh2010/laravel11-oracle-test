<?php

namespace App\Http\Requests\InfoUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateInfoUserRequest extends FormRequest
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
            'tdl_username' =>       'required|string|max:100',
            'dob' =>                'nullable|numeric|regex:/^\d{14}$/',
 
            'tdl_email' =>          'nullable|string|max:100|email',                       
            'tdl_mobile' =>         'nullable|string|max:20|regex:/^[0-9]+$/', 
            'diploma' =>            'nullable|string|max:50', 
            'title' =>              'nullable|string|max:100',
            'account_number' =>                 'nullable|string|max:50', 
            'bank' =>                           'nullable|string|max:200', 


            'department_id' =>          [
                                            'nullable',
                                            'integer',
                                            Rule::exists('App\Models\HIS\Department', 'id')
                                            ->where(function ($query) {
                                                $query = $query
                                                ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                            }),
                                        ], 
            'default_medi_stock_ids' => 'nullable|string|max:10',
            'erx_loginname' =>          'nullable|string|max:100',
            'erx_password' =>           'nullable|string|max:400',
            'social_insurance_number' =>'nullable|string|max:20',


        ];
    }
    public function messages()
    {
        return [
            'tdl_username.required'    => config('keywords')['emp_user']['tdl_username'].config('keywords')['error']['required'],
            'tdl_username.string'      => config('keywords')['emp_user']['tdl_username'].config('keywords')['error']['string'],
            'tdl_username.max'         => config('keywords')['emp_user']['tdl_username'].config('keywords')['error']['string_max'],

            'dob.numeric'      => config('keywords')['emp_user']['dob'].config('keywords')['error']['numeric'],
            'dob.regex'              => config('keywords')['emp_user']['dob'].config('keywords')['error']['regex_ymdhis'],

            'tdl_email.string'      => config('keywords')['emp_user']['tdl_email'].config('keywords')['error']['string'],
            'tdl_email.max'         => config('keywords')['emp_user']['tdl_email'].config('keywords')['error']['string_max'],
            'tdl_email.email'         => config('keywords')['emp_user']['tdl_email'].config('keywords')['error']['email'],

            'tdl_mobile.string'      => config('keywords')['emp_user']['tdl_mobile'].config('keywords')['error']['string'],
            'tdl_mobile.max'         => config('keywords')['emp_user']['tdl_mobile'].config('keywords')['error']['string_max'],
            'tdl_mobile.regex'         => config('keywords')['emp_user']['tdl_mobile'].config('keywords')['error']['regex_phone'],

            'diploma.string'      => config('keywords')['emp_user']['diploma'].config('keywords')['error']['string'],
            'diploma.max'         => config('keywords')['emp_user']['diploma'].config('keywords')['error']['string_max'],

            'title.string'      => config('keywords')['emp_user']['title'].config('keywords')['error']['string'],
            'title.max'         => config('keywords')['emp_user']['title'].config('keywords')['error']['string_max'],


            'account_number.string'      => config('keywords')['emp_user']['account_number'].config('keywords')['error']['string'],
            'account_number.max'         => config('keywords')['emp_user']['account_number'].config('keywords')['error']['string_max'],

            'bank.string'      => config('keywords')['emp_user']['bank'].config('keywords')['error']['string'],
            'bank.max'         => config('keywords')['emp_user']['bank'].config('keywords')['error']['string_max'],


            'department_id.integer'     => config('keywords')['emp_user']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'      => config('keywords')['emp_user']['department_id'].config('keywords')['error']['exists'],

            'default_medi_stock_ids.string'      => config('keywords')['emp_user']['default_medi_stock_ids'].config('keywords')['error']['string'],
            'default_medi_stock_ids.max'         => config('keywords')['emp_user']['default_medi_stock_ids'].config('keywords')['error']['string_max'],

            'erx_loginname.string'      => config('keywords')['emp_user']['erx_loginname'].config('keywords')['error']['string'],
            'erx_loginname.max'         => config('keywords')['emp_user']['erx_loginname'].config('keywords')['error']['string_max'],

            'erx_password.string'      => config('keywords')['emp_user']['erx_password'].config('keywords')['error']['string'],
            'erx_password.max'         => config('keywords')['emp_user']['erx_password'].config('keywords')['error']['string_max'],

            'social_insurance_number.string'      => config('keywords')['emp_user']['social_insurance_number'].config('keywords')['error']['string'],
            'social_insurance_number.max'         => config('keywords')['emp_user']['social_insurance_number'].config('keywords')['error']['string_max'],

        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('default_medi_stock_ids')) {
            $this->merge([
                'default_medi_stock_ids_list' => explode(',', $this->default_medi_stock_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('default_medi_stock_ids_list') && ($this->default_medi_stock_ids_list[0] != null)) {
                foreach ($this->default_medi_stock_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MediStock::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('default_medi_stock_ids', 'Kho mặc định với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
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
