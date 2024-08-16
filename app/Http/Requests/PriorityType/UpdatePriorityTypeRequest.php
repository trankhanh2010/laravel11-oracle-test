<?php

namespace App\Http\Requests\PriorityType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdatePriorityTypeRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'priority_type_code' => [
                                                'required',
                                                'string',
                                                'max:2',
                                                Rule::unique('App\Models\HIS\PriorityType')->ignore($this->id),
                                            ],
            'priority_type_name' =>         'required|string|max:100',
            'age_from' =>                   'nullable|integer|min:0',
            'age_to' =>                     'nullable|integer|min:0|gt:age_from',
            'bhyt_prefixs' =>               'nullable|string|max:4000',
            'is_for_exam_subclinical' =>    'nullable|integer|in:0,1',
            'is_for_prescription' =>        'nullable|integer|in:0,1',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'priority_type_code.required'    => config('keywords')['priority_type']['priority_type_code'].config('keywords')['error']['required'],
            'priority_type_code.string'      => config('keywords')['priority_type']['priority_type_code'].config('keywords')['error']['string'],
            'priority_type_code.max'         => config('keywords')['priority_type']['priority_type_code'].config('keywords')['error']['string_max'],
            'priority_type_code.unique'      => config('keywords')['priority_type']['priority_type_code'].config('keywords')['error']['unique'],

            'priority_type_name.required'    => config('keywords')['priority_type']['priority_type_name'].config('keywords')['error']['required'],
            'priority_type_name.string'      => config('keywords')['priority_type']['priority_type_name'].config('keywords')['error']['string'],
            'priority_type_name.max'         => config('keywords')['priority_type']['priority_type_name'].config('keywords')['error']['string_max'],

            'age_from.integer'      => config('keywords')['priority_type']['age_from'].config('keywords')['error']['integer'],
            'age_from.min'         => config('keywords')['priority_type']['age_from'].config('keywords')['error']['integer_min'],

            'age_to.integer'      => config('keywords')['priority_type']['age_to'].config('keywords')['error']['integer'],
            'age_to.min'         => config('keywords')['priority_type']['age_to'].config('keywords')['error']['integer_min'],
            'age_to.gt'         => config('keywords')['priority_type']['age_to'].config('keywords')['error']['gt'],

            'bhyt_prefixs.string'      => config('keywords')['priority_type']['bhyt_prefixs'].config('keywords')['error']['string'],
            'bhyt_prefixs.max'         => config('keywords')['priority_type']['bhyt_prefixs'].config('keywords')['error']['string_max'],

            'is_for_exam_subclinical.integer'      => config('keywords')['priority_type']['is_for_exam_subclinical'].config('keywords')['error']['integer'],
            'is_for_exam_subclinical.in'         => config('keywords')['priority_type']['is_for_exam_subclinical'].config('keywords')['error']['in'],

            'is_for_prescription.integer'      => config('keywords')['priority_type']['is_for_prescription'].config('keywords')['error']['integer'],
            'is_for_prescription.in'         => config('keywords')['priority_type']['is_for_prescription'].config('keywords')['error']['in'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('bhyt_prefixs')) {
            $this->merge([
                'bhyt_prefixs_list' => explode(',', $this->bhyt_prefixs),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('bhyt_prefixs_list') && ($this->bhyt_prefixs_list[0] != null)) {
                foreach ($this->bhyt_prefixs_list as $id) {
                    if (!is_string($id) || !\App\Models\HIS\BHYTWhitelist::where('bhyt_whitelist_code', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('bhyt_prefixs', 'Đầu mã thẻ BHYT với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
