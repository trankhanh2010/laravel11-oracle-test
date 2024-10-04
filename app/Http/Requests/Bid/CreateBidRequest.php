<?php

namespace App\Http\Requests\Bid;

use App\Models\HIS\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateBidRequest extends FormRequest
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
            'bid_number' =>      'required|string|max:30|unique:App\Models\HIS\Bid,bid_number',
            'bid_name' =>      'required|string|max:500',
            'bid_type_id'   =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\BidType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'bid_year' => 'nullable|string|max:20',
            'valid_from_time' =>  'nullable|integer|regex:/^\d{14}$/',
            'valid_to_time'  =>  'nullable|integer|regex:/^\d{14}$/|gt:valid_from_time',  
            'allow_update_loginnames' => 'nullable|string|max:4000',
            'approval_time' => 'nullable|integer',
            'approval_loginname'  =>                 [
                'nullable',
                'string',
                'max:50',
                Rule::exists('App\Models\HIS\Employee', 'loginname')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'approval_username' =>                  [
                'nullable',
                'string',
                'max:100',
                Rule::exists('App\Models\HIS\Employee', 'tdl_username')
                ->where(function ($query){
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                    ->where(DB::connection('oracle_his')->raw("loginname"), $this->approval_loginname);
                }),
            ],   
            'bid_extra_code'  => 'nullable|string|max:50',
            'bid_form_id'  => 'nullable|in:1,2,3,4,5,6',
            'bid_apthau_code'  => 'nullable|string|max:20',
        ];
    }
    public function messages()
    {
        return [
            'bid_number.required'    => config('keywords')['bid']['bid_number'].config('keywords')['error']['required'],
            'bid_number.string'      => config('keywords')['bid']['bid_number'].config('keywords')['error']['string'],
            'bid_number.max'         => config('keywords')['bid']['bid_number'].config('keywords')['error']['string_max'],
            'bid_number.unique'      => config('keywords')['bid']['bid_number'].config('keywords')['error']['unique'],

            'bid_name.string'      => config('keywords')['bid']['bid_name'].config('keywords')['error']['string'],
            'bid_name.max'         => config('keywords')['bid']['bid_name'].config('keywords')['error']['string_max'],
            'bid_name.unique'      => config('keywords')['bid']['bid_name'].config('keywords')['error']['unique'],

            'bid_type_id.integer'     => config('keywords')['bid']['bid_type_id'].config('keywords')['error']['integer'],
            'bid_type_id.exists'      => config('keywords')['bid']['bid_type_id'].config('keywords')['error']['exists'],

            'bid_year.string'      => config('keywords')['bid']['bid_year'].config('keywords')['error']['string'],
            'bid_year.max'         => config('keywords')['bid']['bid_year'].config('keywords')['error']['string_max'],

            'valid_from_time.integer'            => config('keywords')['bid']['valid_from_time'].config('keywords')['error']['integer'],
            'valid_from_time.regex'              => config('keywords')['bid']['valid_from_time'].config('keywords')['error']['regex_ymdhis'],
            
            'valid_to_time.integer'            => config('keywords')['bid']['valid_to_time'].config('keywords')['error']['integer'],
            'valid_to_time.regex'              => config('keywords')['bid']['valid_to_time'].config('keywords')['error']['regex_ymdhis'],
            'valid_to_time.gt'                 => config('keywords')['bid']['valid_to_time'].config('keywords')['error']['gt'],

            'allow_update_loginnames.string'      => config('keywords')['bid']['allow_update_loginnames'].config('keywords')['error']['string'],
            'allow_update_loginnames.max'         => config('keywords')['bid']['allow_update_loginnames'].config('keywords')['error']['string_max'],

            'approval_time.integer'     => config('keywords')['bid']['approval_time'].config('keywords')['error']['integer'],

            'approval_loginname.string'     => config('keywords')['bid']['approval_loginname'].config('keywords')['error']['string'],
            'approval_loginname.max'        => config('keywords')['bid']['approval_loginname'].config('keywords')['error']['string_max'], 
            'approval_loginname.exists'     => config('keywords')['bid']['approval_loginname'].config('keywords')['error']['exists'],  

            'approval_username.string'  => config('keywords')['bid']['approval_username'].config('keywords')['error']['string'],
            'approval_username.max'     => config('keywords')['bid']['approval_username'].config('keywords')['error']['string_max'], 
            'approval_username.exists'  => config('keywords')['bid']['approval_username'].config('keywords')['error']['exists'].config('keywords')['error']['not_in_loginname'],  

            'bid_extra_code.string'      => config('keywords')['bid']['bid_extra_code'].config('keywords')['error']['string'],
            'bid_extra_code.max'         => config('keywords')['bid']['bid_extra_code'].config('keywords')['error']['string_max'],

            'bid_form_id.integer'     => config('keywords')['bid']['bid_form_id'].config('keywords')['error']['integer'],
            'bid_form_id.in'          => config('keywords')['bid']['bid_form_id'].config('keywords')['error']['in'],

            'bid_apthau_code.string'      => config('keywords')['bid']['bid_apthau_code'].config('keywords')['error']['string'],
            'bid_apthau_code.max'         => config('keywords')['bid']['bid_apthau_code'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('allow_update_loginnames')) {
            $this->merge([
                'allow_update_loginnames_list' => explode(',', $this->allow_update_loginnames),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('allow_update_loginnames_list') && ($this->allow_update_loginnames_list[0] != null)) {
                foreach ($this->allow_update_loginnames_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\Employee::where('loginname', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('allow_update_loginnames', 'Người dùng với loginname = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
