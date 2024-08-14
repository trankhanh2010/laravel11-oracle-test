<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateEmployeeRequest extends FormRequest
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
     */    public function rules()
    {
        return [
            'tdl_username' =>       'required|string|max:100',
            'dob' =>                'nullable|numeric|regex:/^\d{14}$/',
            'gender_id' =>          [
                                        'nullable',
                                        'integer',
                                        Rule::exists('App\Models\HIS\Gender', 'id')
                                        ->where(function ($query) {
                                            $query = $query
                                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                        }),
                                    ], 
            'ethnic_code' =>        [
                                        'nullable',
                                        'string',
                                        'max:2',
                                        Rule::exists('App\Models\SDA\Ethnic', 'ethnic_code')
                                        ->where(function ($query) {
                                            $query = $query
                                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                        }),
                                    ],


            'tdl_email' =>          'nullable|string|max:100|email',                       
            'tdl_mobile' =>         'nullable|string|max:20|regex:/^[0-9]+$/', 
            'diploma' =>            'nullable|string|max:50', 
            'diploma_date' =>       'nullable|integer|regex:/^\d{14}$/',         
            'diploma_place' =>      'nullable|string|max:50',
            'title' =>              'nullable|string|max:100',


            'medicine_type_rank' =>             'nullable|integer|in:1,2,3,4,5',
            'max_bhyt_service_req_per_day' =>   [
                                                    'nullable',
                                                    'integer',
                                                    'min:0',
                                                    function ($attribute, $value, $fail)  {
                                                        if (!is_null($this->max_service_req_per_day) && !is_null($value)) {
                                                            $fail(config('keywords')['emp_user']['max_bhyt_service_req_per_day'].' chỉ được nhập khi '.config('keywords')['emp_user']['max_service_req_per_day'].' trống!');
                                                        }
                                                    },
                                                ],
            'max_service_req_per_day' =>        [
                                                    'nullable',
                                                    'integer',
                                                    'min:0',
                                                    function ($attribute, $value, $fail)  {
                                                        if (!is_null($this->max_bhyt_service_req_per_day) && !is_null($value)) {
                                                            $fail(config('keywords')['emp_user']['max_service_req_per_day'].' chỉ được nhập khi '.config('keywords')['emp_user']['max_bhyt_service_req_per_day'].' trống!');
                                                        }
                                                    },
                                                ],
            'is_service_req_exam'  =>           [
                                                    'nullable',
                                                    'integer',
                                                    'in:0,1',
                                                    function ($attribute, $value, $fail)  {
                                                        if (is_null($this->max_bhyt_service_req_per_day) && is_null($this->max_service_req_per_day)) {
                                                            $fail(config('keywords')['emp_user']['is_service_req_exam'].' chỉ được chọn khi '.config('keywords')['emp_user']['max_service_req_per_day'].' hoặc '.config('keywords')['emp_user']['max_bhyt_service_req_per_day'].' đã được nhập!');
                                                        }
                                                    },
                                                ],
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
            'identification_number' =>  'nullable|string|max:15',
            'social_insurance_number' =>'nullable|string|max:20',
            'career_title_id' =>        [
                                            'nullable',
                                            'integer',
                                            Rule::exists('App\Models\HIS\CareerTitle', 'id')
                                            ->where(function ($query) {
                                                $query = $query
                                                ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                            }),
                                        ], 

            
            'position' =>           'nullable|integer|in:1,2,3',
            'speciality_codes' =>   'nullable|string|max:15',
            'type_of_time' =>       'nullable|integer|in:1,2',
            'branch_id' =>          [
                                        'nullable',
                                        'integer',
                                        Rule::exists('App\Models\HIS\Branch', 'id')
                                        ->where(function ($query) {
                                            $query = $query
                                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                        }),
                                    ], 
            'medi_org_codes' =>     'nullable|string|max:30',
            'is_doctor' =>          'nullable|integer|in:0,1',

            'is_nurse' =>                       'nullable|integer|in:0,1',
            'is_admin' =>                       'nullable|integer|in:0,1',
            'allow_update_other_sclinical' =>   'nullable|integer|in:0,1',
            'do_not_allow_simultaneity' =>      'nullable|integer|in:0,1',
            'is_limit_schedule' =>              'nullable|integer|in:0,1',
            'is_need_sign_instead' =>           'nullable|integer|in:0,1',
            'is_active' =>               'required|integer|in:0,1'

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

            'gender_id.integer'     => config('keywords')['emp_user']['gender_id'].config('keywords')['error']['integer'],
            'gender_id.exists'      => config('keywords')['emp_user']['gender_id'].config('keywords')['error']['exists'],

            'ethnic_code.string'      => config('keywords')['emp_user']['ethnic_code'].config('keywords')['error']['string'],
            'ethnic_code.max'         => config('keywords')['emp_user']['ethnic_code'].config('keywords')['error']['string_max'],
            'ethnic_code.exists'      => config('keywords')['emp_user']['ethnic_code'].config('keywords')['error']['exists'],


            'tdl_email.string'      => config('keywords')['emp_user']['tdl_email'].config('keywords')['error']['string'],
            'tdl_email.max'         => config('keywords')['emp_user']['tdl_email'].config('keywords')['error']['string_max'],
            'tdl_email.email'         => config('keywords')['emp_user']['tdl_email'].config('keywords')['error']['email'],

            'tdl_mobile.string'      => config('keywords')['emp_user']['tdl_mobile'].config('keywords')['error']['string'],
            'tdl_mobile.max'         => config('keywords')['emp_user']['tdl_mobile'].config('keywords')['error']['string_max'],
            'tdl_mobile.regex'         => config('keywords')['emp_user']['tdl_mobile'].config('keywords')['error']['regex_phone'],

            'diploma.string'      => config('keywords')['emp_user']['diploma'].config('keywords')['error']['string'],
            'diploma.max'         => config('keywords')['emp_user']['diploma'].config('keywords')['error']['string_max'],

            'diploma_date.integer'            => config('keywords')['emp_user']['diploma_date'].config('keywords')['error']['integer'],
            'diploma_date.regex'              => config('keywords')['emp_user']['diploma_date'].config('keywords')['error']['regex_ymdhis'],

            'diploma_place.string'      => config('keywords')['emp_user']['diploma_place'].config('keywords')['error']['string'],
            'diploma_place.max'         => config('keywords')['emp_user']['diploma_place'].config('keywords')['error']['string_max'],

            'title.string'      => config('keywords')['emp_user']['title'].config('keywords')['error']['string'],
            'title.max'         => config('keywords')['emp_user']['title'].config('keywords')['error']['string_max'],


            'medicine_type_rank.integer'     => config('keywords')['emp_user']['medicine_type_rank'].config('keywords')['error']['integer'],
            'medicine_type_rank.in'      => config('keywords')['emp_user']['medicine_type_rank'].config('keywords')['error']['in'],

            'max_bhyt_service_req_per_day.integer'     => config('keywords')['emp_user']['max_bhyt_service_req_per_day'].config('keywords')['error']['integer'],
            'max_bhyt_service_req_per_day.min'      => config('keywords')['emp_user']['max_bhyt_service_req_per_day'].config('keywords')['error']['integer_min'],

            'max_service_req_per_day.integer'     => config('keywords')['emp_user']['max_service_req_per_day'].config('keywords')['error']['integer'],
            'max_service_req_per_day.min'      => config('keywords')['emp_user']['max_service_req_per_day'].config('keywords')['error']['integer_min'],

            'is_service_req_exam.integer'     => config('keywords')['emp_user']['is_service_req_exam'].config('keywords')['error']['integer'],
            'is_service_req_exam.in'      => config('keywords')['emp_user']['is_service_req_exam'].config('keywords')['error']['in'],

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

            'identification_number.string'      => config('keywords')['emp_user']['identification_number'].config('keywords')['error']['string'],
            'identification_number.max'         => config('keywords')['emp_user']['identification_number'].config('keywords')['error']['string_max'],

            'social_insurance_number.string'      => config('keywords')['emp_user']['social_insurance_number'].config('keywords')['error']['string'],
            'social_insurance_number.max'         => config('keywords')['emp_user']['social_insurance_number'].config('keywords')['error']['string_max'],

            'career_title_id.integer'     => config('keywords')['emp_user']['career_title_id'].config('keywords')['error']['integer'],
            'career_title_id.exists'      => config('keywords')['emp_user']['career_title_id'].config('keywords')['error']['exists'],


            'position.integer'     => config('keywords')['emp_user']['position'].config('keywords')['error']['integer'],
            'position.in'      => config('keywords')['emp_user']['position'].config('keywords')['error']['in'],

            'speciality_codes.string'      => config('keywords')['emp_user']['speciality_codes'].config('keywords')['error']['string'],
            'speciality_codes.max'         => config('keywords')['emp_user']['speciality_codes'].config('keywords')['error']['string_max'],

            'type_of_time.integer'     => config('keywords')['emp_user']['type_of_time'].config('keywords')['error']['integer'],
            'type_of_time.in'      => config('keywords')['emp_user']['type_of_time'].config('keywords')['error']['in'],

            'branch_id.integer'     => config('keywords')['emp_user']['branch_id'].config('keywords')['error']['integer'],
            'branch_id.exists'      => config('keywords')['emp_user']['branch_id'].config('keywords')['error']['exists'],

            'medi_org_codes.string'      => config('keywords')['emp_user']['medi_org_codes'].config('keywords')['error']['string'],
            'medi_org_codes.max'         => config('keywords')['emp_user']['medi_org_codes'].config('keywords')['error']['string_max'],

            'is_doctor.integer'     => config('keywords')['emp_user']['is_doctor'].config('keywords')['error']['integer'],
            'is_doctor.in'      => config('keywords')['emp_user']['is_doctor'].config('keywords')['error']['in'],

            'is_nurse.integer'     => config('keywords')['emp_user']['is_nurse'].config('keywords')['error']['integer'],
            'is_nurse.in'      => config('keywords')['emp_user']['is_nurse'].config('keywords')['error']['in'],

            'is_admin.integer'     => config('keywords')['emp_user']['is_admin'].config('keywords')['error']['integer'],
            'is_admin.in'      => config('keywords')['emp_user']['is_admin'].config('keywords')['error']['in'],

            'allow_update_other_sclinical.integer'     => config('keywords')['emp_user']['allow_update_other_sclinical'].config('keywords')['error']['integer'],
            'allow_update_other_sclinical.in'      => config('keywords')['emp_user']['allow_update_other_sclinical'].config('keywords')['error']['in'],

            'do_not_allow_simultaneity.integer'     => config('keywords')['emp_user']['do_not_allow_simultaneity'].config('keywords')['error']['integer'],
            'do_not_allow_simultaneity.in'      => config('keywords')['emp_user']['do_not_allow_simultaneity'].config('keywords')['error']['in'],

            'is_limit_schedule.integer'     => config('keywords')['emp_user']['is_limit_schedule'].config('keywords')['error']['integer'],
            'is_limit_schedule.in'      => config('keywords')['emp_user']['is_limit_schedule'].config('keywords')['error']['in'],

            'is_need_sign_instead.integer'     => config('keywords')['emp_user']['is_need_sign_instead'].config('keywords')['error']['integer'],
            'is_need_sign_instead.in'      => config('keywords')['emp_user']['is_need_sign_instead'].config('keywords')['error']['in'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('default_medi_stock_ids')) {
            $this->merge([
                'default_medi_stock_ids_list' => explode(',', $this->default_medi_stock_ids),
            ]);
        }
        if ($this->has('speciality_codes')) {
            $this->merge([
                'speciality_codes_list' => explode(',', $this->speciality_codes),
            ]);
        }
        if ($this->has('medi_org_codes')) {
            $this->merge([
                'medi_org_codes_list' => explode(',', $this->medi_org_codes),
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
            if ($this->has('speciality_codes_list') && ($this->speciality_codes_list[0] != null)) {
                foreach ($this->speciality_codes_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\Speciality::where('speciality_code', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('speciality_codes', 'Phạm vi chuyên môn với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('medi_org_codes_list') && ($this->medi_org_codes_list[0] != null)) {
                foreach ($this->medi_org_codes_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\MediOrg::where('medi_org_code', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medi_org_codes', 'Cơ sở khám chữa bệnh khác với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
