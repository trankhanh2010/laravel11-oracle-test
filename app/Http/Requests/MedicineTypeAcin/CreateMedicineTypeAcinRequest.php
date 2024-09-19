<?php

namespace App\Http\Requests\MedicineTypeAcin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateMedicineTypeAcinRequest extends FormRequest
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
            'active_ingredient_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ActiveIngredient', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'medicine_type_ids' => 'nullable|string|max:4000',

            'medicine_type_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicineType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'active_ingredient_ids' => 'nullable|string|max:4000',
        ];
    }
    public function messages()
    {
        return [
            'active_ingredient_id.integer'     => config('keywords')['medicine_type_acin']['active_ingredient_id'].config('keywords')['error']['integer'],
            'active_ingredient_id.exists'      => config('keywords')['medicine_type_acin']['active_ingredient_id'].config('keywords')['error']['exists'],

            'medicine_type_ids.string'     => config('keywords')['medicine_type_acin']['medicine_type_ids'].config('keywords')['error']['string'],
            'medicine_type_ids.max'      => config('keywords')['medicine_type_acin']['medicine_type_ids'].config('keywords')['error']['string_max'],

            'medicine_type_id.integer'     => config('keywords')['medicine_type_acin']['medicine_type_id'].config('keywords')['error']['integer'],
            'medicine_type_id.exists'      => config('keywords')['medicine_type_acin']['medicine_type_id'].config('keywords')['error']['exists'],

            'active_ingredient_ids.string'     => config('keywords')['medicine_type_acin']['active_ingredient_ids'].config('keywords')['error']['string'],
            'active_ingredient_ids.max'      => config('keywords')['medicine_type_acin']['active_ingredient_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('medicine_type_ids')) {
            $this->merge([
                'medicine_type_ids_list' => explode(',', $this->medicine_type_ids),
            ]);
        }
        if ($this->has('active_ingredient_ids')) {
            $this->merge([
                'active_ingredient_ids_list' => explode(',', $this->active_ingredient_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->medicine_type_id === null) && ($this->active_ingredient_id === null)) {
                $validator->errors()->add('medicine_type_id',config('keywords')['medicine_type_acin']['active_ingredient_id'].' và '.config('keywords')['medicine_type_acin']['medicine_type_id'].' không thể cùng trống!');
                $validator->errors()->add('active_ingredient_id',config('keywords')['medicine_type_acin']['active_ingredient_id'].' và '.config('keywords')['medicine_type_acin']['medicine_type_id'].' không thể cùng trống!');
            }
            if (($this->medicine_type_id !== null) && ($this->active_ingredient_id !== null)) {
                $validator->errors()->add('medicine_type_id',config('keywords')['medicine_type_acin']['active_ingredient_id'].' và '.config('keywords')['medicine_type_acin']['medicine_type_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('active_ingredient_id',config('keywords')['medicine_type_acin']['active_ingredient_id'].' và '.config('keywords')['medicine_type_acin']['medicine_type_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('medicine_type_ids_list') && ($this->medicine_type_ids_list[0] != null)) {
                foreach ($this->medicine_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MedicineType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medicine_type_ids', 'Phòng chỉ định với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('active_ingredient_ids_list') && ($this->active_ingredient_ids_list[0] != null)) {
                foreach ($this->active_ingredient_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\ActiveIngredient::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('active_ingredient_ids', 'Phòng thực hiện với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
