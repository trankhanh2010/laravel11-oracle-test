<?php

namespace App\Http\Requests\ModuleRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateModuleRoleRequest extends FormRequest
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
            'module_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\ACS\Module', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_acs')->raw("is_active"), 1);
                }),
            ],
            'role_ids' => 'nullable|string|max:4000',

            'role_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\ACS\Role', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_acs')->raw("is_active"), 1);
                }),
            ],
            'module_ids' => 'nullable|string|max:4000',
        ];
    }
    public function messages()
    {
        return [
            'module_id.integer'     => config('keywords')['module_role']['module_id'].config('keywords')['error']['integer'],
            'module_id.exists'      => config('keywords')['module_role']['module_id'].config('keywords')['error']['exists'],

            'role_ids.string'     => config('keywords')['module_role']['role_ids'].config('keywords')['error']['string'],
            'role_ids.max'      => config('keywords')['module_role']['role_ids'].config('keywords')['error']['string_max'],

            'role_id.integer'     => config('keywords')['module_role']['role_id'].config('keywords')['error']['integer'],
            'role_id.exists'      => config('keywords')['module_role']['role_id'].config('keywords')['error']['exists'],

            'module_ids.string'     => config('keywords')['module_role']['module_ids'].config('keywords')['error']['string'],
            'module_ids.max'      => config('keywords')['module_role']['module_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('role_ids')) {
            $this->merge([
                'role_ids_list' => explode(',', $this->role_ids),
            ]);
        }
        if ($this->has('module_ids')) {
            $this->merge([
                'module_ids_list' => explode(',', $this->module_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->role_id === null) && ($this->module_id === null)) {
                $validator->errors()->add('role_id',config('keywords')['module_role']['module_id'].' và '.config('keywords')['module_role']['role_id'].' không thể cùng trống!');
                $validator->errors()->add('module_id',config('keywords')['module_role']['module_id'].' và '.config('keywords')['module_role']['role_id'].' không thể cùng trống!');
            }
            if (($this->role_id !== null) && ($this->module_id !== null)) {
                $validator->errors()->add('role_id',config('keywords')['module_role']['module_id'].' và '.config('keywords')['module_role']['role_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('module_id',config('keywords')['module_role']['module_id'].' và '.config('keywords')['module_role']['role_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('role_ids_list') && ($this->role_ids_list[0] != null)) {
                foreach ($this->role_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\ACS\Role::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('role_ids', 'Chức năng với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('module_ids_list') && ($this->module_ids_list[0] != null)) {
                foreach ($this->module_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\ACS\Module::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('module_ids', 'Vai trò với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
