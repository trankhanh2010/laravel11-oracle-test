<?php

namespace App\Http\Requests\PatientClassify;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdatePatientClassifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'patient_classify_code' =>      [
                                                'required',
                                                'string',
                                                'max:10',
                                                Rule::unique('App\Models\HIS\PatientClassify')->ignore($this->id),
                                            ],
            'patient_classify_name' =>      'required|string|max:100',
            'display_color' =>              'required|string|max:20|rgb_color',
            'patient_type_id' =>            'nullable|integer|exists:App\Models\HIS\PatientType,id',
            'other_pay_source_id' =>        'nullable|integer|exists:App\Models\HIS\OtherPaySource,id',
            'bhyt_whitelist_ids' =>         'nullable|string|max:500',
            'military_rank_ids' =>          'nullable|string|max:500',
            'is_police' =>                  'nullable|integer|in:0,1',
            'is_active' =>                  'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'patient_classify_code.required'    => config('keywords')['patient_classify']['patient_classify_code'].config('keywords')['error']['required'],
            'patient_classify_code.string'      => config('keywords')['patient_classify']['patient_classify_code'].config('keywords')['error']['string'],
            'patient_classify_code.max'         => config('keywords')['patient_classify']['patient_classify_code'].config('keywords')['error']['string_max'],
            'patient_classify_code.unique'      => config('keywords')['patient_classify']['patient_classify_code'].config('keywords')['error']['unique'],

            'patient_classify_name.required'    => config('keywords')['patient_classify']['patient_classify_name'].config('keywords')['error']['required'],
            'patient_classify_name.string'      => config('keywords')['patient_classify']['patient_classify_name'].config('keywords')['error']['string'],
            'patient_classify_name.max'         => config('keywords')['patient_classify']['patient_classify_name'].config('keywords')['error']['string_max'],

            'display_color.required'    => config('keywords')['patient_classify']['display_color'].config('keywords')['error']['required'],
            'display_color.string'      => config('keywords')['patient_classify']['display_color'].config('keywords')['error']['string'],
            'display_color.max'         => config('keywords')['patient_classify']['display_color'].config('keywords')['error']['string_max'],
            // 'display_color.rgb_color'   => config('keywords')['patient_classify']['display_color'].' = '.$this->display_color.' không phải mã màu RGB!',

            'patient_type_id.integer'       => config('keywords')['patient_classify']['patient_type_id'].config('keywords')['error']['integer'],
            'patient_type_id.exists'        => config('keywords')['patient_classify']['patient_type_id'].config('keywords')['error']['exists'],

            'other_pay_source_id.integer'       => config('keywords')['patient_classify']['other_pay_source_id'].config('keywords')['error']['integer'],
            'other_pay_source_id.exists'        => config('keywords')['patient_classify']['other_pay_source_id'].config('keywords')['error']['exists'],

            'is_police.integer'       => config('keywords')['patient_classify']['is_police'].config('keywords')['error']['integer'],
            'is_police.in'            => config('keywords')['patient_classify']['is_police'].config('keywords')['error']['in'],

            'bhyt_whitelist_ids.string'      => config('keywords')['patient_classify']['bhyt_whitelist_ids'].config('keywords')['error']['string'],
            'bhyt_whitelist_ids.max'         => config('keywords')['patient_classify']['bhyt_whitelist_ids'].config('keywords')['error']['string_max'],

            'military_rank_ids.string'      => config('keywords')['patient_classify']['military_rank_ids'].config('keywords')['error']['string'],
            'military_rank_ids.max'         => config('keywords')['patient_classify']['military_rank_ids'].config('keywords')['error']['string_max'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('bhyt_whitelist_ids')) {
            $this->merge([
                'bhyt_whitelist_ids_list' => explode(',', $this->bhyt_whitelist_ids),
            ]);
        }
        if ($this->has('military_rank_ids')) {
            $this->merge([
                'military_rank_ids_list' => explode(',', $this->military_rank_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('bhyt_whitelist_ids_list') && ($this->bhyt_whitelist_ids_list[0] != null)) {
                foreach ($this->bhyt_whitelist_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\BHYTWhitelist::find($id)) {
                        $validator->errors()->add('bhyt_whitelist_ids', 'Đầu mã BHYT với id = ' . $id . ' trong danh sách đầu mã BHYT không tồn tại!');
                    }
                }
            }
            ///////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($this->has('military_rank_ids_list') && ($this->military_rank_ids_list[0] != null)) {
                foreach ($this->military_rank_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MilitaryRank::find($id)) {
                        $validator->errors()->add('military_rank_ids', 'Quân hàm với id = ' . $id . ' trong danh sách quân hàm không tồn tại!');
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
