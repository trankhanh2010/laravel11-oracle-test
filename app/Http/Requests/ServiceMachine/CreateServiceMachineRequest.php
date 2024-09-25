<?php

namespace App\Http\Requests\ServiceMachine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateServiceMachineRequest extends FormRequest
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
            'service_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'machine_ids' => 'nullable|string|max:4000',

            'machine_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Machine', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'service_ids' => 'nullable|string|max:4000',
        ];
    }
    public function messages()
    {
        return [
            'service_id.integer'     => config('keywords')['service_machine']['service_id'].config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['service_machine']['service_id'].config('keywords')['error']['exists'],

            'machine_ids.string'     => config('keywords')['service_machine']['machine_ids'].config('keywords')['error']['string'],
            'machine_ids.max'      => config('keywords')['service_machine']['machine_ids'].config('keywords')['error']['string_max'],

            'machine_id.integer'     => config('keywords')['service_machine']['machine_id'].config('keywords')['error']['integer'],
            'machine_id.exists'      => config('keywords')['service_machine']['machine_id'].config('keywords')['error']['exists'],

            'service_ids.string'     => config('keywords')['service_machine']['service_ids'].config('keywords')['error']['string'],
            'service_ids.max'      => config('keywords')['service_machine']['service_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('machine_ids')) {
            $this->merge([
                'machine_ids_list' => explode(',', $this->machine_ids),
            ]);
        }
        if ($this->has('service_ids')) {
            $this->merge([
                'service_ids_list' => explode(',', $this->service_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->machine_id === null) && ($this->service_id === null)) {
                $validator->errors()->add('machine_id',config('keywords')['service_machine']['service_id'].' và '.config('keywords')['service_machine']['machine_id'].' không thể cùng trống!');
                $validator->errors()->add('service_id',config('keywords')['service_machine']['service_id'].' và '.config('keywords')['service_machine']['machine_id'].' không thể cùng trống!');
            }
            if (($this->machine_id !== null) && ($this->service_id !== null)) {
                $validator->errors()->add('machine_id',config('keywords')['service_machine']['service_id'].' và '.config('keywords')['service_machine']['machine_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('service_id',config('keywords')['service_machine']['service_id'].' và '.config('keywords')['service_machine']['machine_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('machine_ids_list') && ($this->machine_ids_list[0] != null)) {
                foreach ($this->machine_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Machine::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('machine_ids', 'Máy với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('service_ids_list') && ($this->service_ids_list[0] != null)) {
                foreach ($this->service_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Service::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('service_ids', 'Dịch vụ với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
