<?php

namespace App\Http\Requests\MediStockMety;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateMediStockMetyRequest extends FormRequest
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
            'medi_stock_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MediStock', 'id')
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
            'medi_stock_ids' => 'nullable|string|max:4000',
            'is_prevent_max' => 'nullable|integer|in:0,1',
            'is_prevent_exp' => 'nullable|integer|in:0,1',
            'is_goods_restrict' => 'nullable|integer|in:0,1',

        ];
    }
    public function messages()
    {
        return [
            'medi_stock_id.integer'     => config('keywords')['medi_stock_mety']['medi_stock_id'].config('keywords')['error']['integer'],
            'medi_stock_id.exists'      => config('keywords')['medi_stock_mety']['medi_stock_id'].config('keywords')['error']['exists'],

            'medicine_type_ids.string'     => config('keywords')['medi_stock_mety']['medicine_type_ids'].config('keywords')['error']['string'],
            'medicine_type_ids.max'      => config('keywords')['medi_stock_mety']['medicine_type_ids'].config('keywords')['error']['string_max'],

            'medicine_type_id.integer'     => config('keywords')['medi_stock_mety']['medicine_type_id'].config('keywords')['error']['integer'],
            'medicine_type_id.exists'      => config('keywords')['medi_stock_mety']['medicine_type_id'].config('keywords')['error']['exists'],

            'medi_stock_ids.string'     => config('keywords')['medi_stock_mety']['medi_stock_ids'].config('keywords')['error']['string'],
            'medi_stock_ids.max'      => config('keywords')['medi_stock_mety']['medi_stock_ids'].config('keywords')['error']['string_max'],

            'is_prevent_max.integer'    => config('keywords')['medi_stock_mety']['is_prevent_max'].config('keywords')['error']['integer'],
            'is_prevent_max.in'         => config('keywords')['medi_stock_mety']['is_prevent_max'].config('keywords')['error']['in'],

            'is_prevent_exp.integer'    => config('keywords')['medi_stock_mety']['is_prevent_exp'].config('keywords')['error']['integer'],
            'is_prevent_exp.in'         => config('keywords')['medi_stock_mety']['is_prevent_exp'].config('keywords')['error']['in'],

            'is_goods_restrict.integer'    => config('keywords')['medi_stock_mety']['is_goods_restrict'].config('keywords')['error']['integer'],
            'is_goods_restrict.in'         => config('keywords')['medi_stock_mety']['is_goods_restrict'].config('keywords')['error']['in'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('medicine_type_ids')) {
            $this->merge([
                'medicine_type_ids_list' => explode(',', $this->medicine_type_ids),
            ]);
        }
        if ($this->has('medi_stock_ids')) {
            $this->merge([
                'medi_stock_ids_list' => explode(',', $this->medi_stock_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->medicine_type_id === null) && ($this->medi_stock_id === null)) {
                $validator->errors()->add('medicine_type_id',config('keywords')['medi_stock_mety']['medi_stock_id'].' và '.config('keywords')['medi_stock_mety']['medicine_type_id'].' không thể cùng trống!');
                $validator->errors()->add('medi_stock_id',config('keywords')['medi_stock_mety']['medi_stock_id'].' và '.config('keywords')['medi_stock_mety']['medicine_type_id'].' không thể cùng trống!');
            }
            if (($this->medicine_type_id !== null) && ($this->medi_stock_id !== null)) {
                $validator->errors()->add('medicine_type_id',config('keywords')['medi_stock_mety']['medi_stock_id'].' và '.config('keywords')['medi_stock_mety']['medicine_type_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('medi_stock_id',config('keywords')['medi_stock_mety']['medi_stock_id'].' và '.config('keywords')['medi_stock_mety']['medicine_type_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('medicine_type_ids_list') && ($this->medicine_type_ids_list[0] != null)) {
                foreach ($this->medicine_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MedicineType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medicine_type_ids', 'Loại thuốc với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('medi_stock_ids_list') && ($this->medi_stock_ids_list[0] != null)) {
                foreach ($this->medi_stock_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MediStock::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medi_stock_ids', 'Kho với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
