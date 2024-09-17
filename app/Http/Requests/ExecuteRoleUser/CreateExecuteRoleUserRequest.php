<?php

namespace App\Http\Requests\ExecuteRoleUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateExecuteRoleUserRequest extends FormRequest
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
            'execute_role_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ExecuteRole', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'loginnames' => 'nullable|string|max:4000',

            'loginname' =>  [
                'nullable',
                'string',
                Rule::exists('App\Models\HIS\Employee', 'loginname')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'execute_role_ids' => 'nullable|string|max:4000',
        ];
    }
    public function messages()
    {
        return [
            'execute_role_id.integer'     => config('keywords')['execute_role_user']['execute_role_id'].config('keywords')['error']['integer'],
            'execute_role_id.exists'      => config('keywords')['execute_role_user']['execute_role_id'].config('keywords')['error']['exists'],

            'loginnames.string'     => config('keywords')['execute_role_user']['loginnames'].config('keywords')['error']['string'],
            'loginnames.max'      => config('keywords')['execute_role_user']['loginnames'].config('keywords')['error']['string_max'],

            'loginname.string'     => config('keywords')['execute_role_user']['loginname'].config('keywords')['error']['integer'],
            'loginname.exists'      => config('keywords')['execute_role_user']['loginname'].config('keywords')['error']['exists'],

            'execute_role_ids.string'     => config('keywords')['execute_role_user']['execute_role_ids'].config('keywords')['error']['string'],
            'execute_role_ids.max'      => config('keywords')['execute_role_user']['execute_role_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('loginnames')) {
            $this->merge([
                'loginnames_list' => explode(',', $this->loginnames),
            ]);
        }
        if ($this->has('execute_role_ids')) {
            $this->merge([
                'execute_role_ids_list' => explode(',', $this->execute_role_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->loginname === null) && ($this->execute_role_id === null)) {
                $validator->errors()->add('loginname',config('keywords')['execute_role_user']['execute_role_id'].' và '.config('keywords')['execute_role_user']['loginname'].' không thể cùng trống!');
                $validator->errors()->add('execute_role_id',config('keywords')['execute_role_user']['execute_role_id'].' và '.config('keywords')['execute_role_user']['loginname'].' không thể cùng trống!');
            }
            if (($this->loginname !== null) && ($this->execute_role_id !== null)) {
                $validator->errors()->add('loginname',config('keywords')['execute_role_user']['execute_role_id'].' và '.config('keywords')['execute_role_user']['loginname'].' không thể cùng có giá trị!');
                $validator->errors()->add('execute_role_id',config('keywords')['execute_role_user']['execute_role_id'].' và '.config('keywords')['execute_role_user']['loginname'].' không thể cùng có giá trị!');
            }
            if ($this->has('loginnames_list') && ($this->loginnames_list[0] != null)) {
                foreach ($this->loginnames_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\Employee::where('loginname', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('loginnames', 'Loginname = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('execute_role_ids_list') && ($this->execute_role_ids_list[0] != null)) {
                foreach ($this->execute_role_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\ExecuteRole::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('execute_role_ids', 'Vai trò thực hiện với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
