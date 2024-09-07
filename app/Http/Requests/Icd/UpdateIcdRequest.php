<?php

namespace App\Http\Requests\Icd;

use App\Models\HIS\Icd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateIcdRequest extends FormRequest
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
        if(!is_numeric($this->icd)){
            throw new HttpResponseException(returnIdError($this->icd));
        }
        return [
            'icd_code' => [
                                'required',
                                'string',
                                'max:10',
                                Rule::unique('App\Models\HIS\Icd')->ignore($this->icd),
                            ],
            'icd_name' =>           'required|string|max:500',
            'icd_name_en' =>        'nullable|string|max:500',
            'icd_name_common' =>    'nullable|string|max:500',
            'icd_group_id' =>       [
                                        'nullable',
                                        'integer',
                                        Rule::exists('App\Models\HIS\IcdGroup', 'id')
                                        ->where(function ($query) {
                                            $query = $query
                                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                        }),
                                    ], 
            'attach_icd_codes' =>    'nullable|string|max:4000',

            'age_from' =>       'nullable|integer|min:0',
            'age_to' =>         'nullable|integer|min:0|gt:age_from',
            'age_type_id' =>    [
                                    'nullable',
                                    'integer',
                                    Rule::exists('App\Models\HIS\AgeType', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'gender_id' =>      [
                                    'nullable',
                                    'integer',
                                    Rule::exists('App\Models\HIS\Gender', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'is_sword' =>       'nullable|integer|in:0,1',
            'is_subcode' =>     'nullable|integer|in:0,1',


            'is_latent_tuberculosis' => 'nullable|integer|in:0,1',
            'is_cause' =>               'nullable|integer|in:0,1',
            'is_hein_nds' =>            'nullable|integer|in:0,1',
            'is_require_cause' =>       'nullable|integer|in:0,1',
            'is_traditional' =>         'nullable|integer|in:0,1',
            'unable_for_treatment' =>   'nullable|integer|in:0,1',

            'do_not_use_hein' =>   'nullable|integer|in:0,1',
            'is_covid' =>   'nullable|integer|in:0,1',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'icd_code.required'    => config('keywords')['icd']['icd_code'].config('keywords')['error']['required'],
            'icd_code.string'      => config('keywords')['icd']['icd_code'].config('keywords')['error']['string'],
            'icd_code.max'         => config('keywords')['icd']['icd_code'].config('keywords')['error']['string_max'],
            'icd_code.unique'      => config('keywords')['icd']['icd_code'].config('keywords')['error']['unique'],

            'icd_name.required'    => config('keywords')['icd']['icd_name'].config('keywords')['error']['required'],
            'icd_name.string'      => config('keywords')['icd']['icd_name'].config('keywords')['error']['string'],
            'icd_name.max'         => config('keywords')['icd']['icd_name'].config('keywords')['error']['string_max'],

            'icd_name_en.string'      => config('keywords')['icd']['icd_name_en'].config('keywords')['error']['string'],
            'icd_name_en.max'         => config('keywords')['icd']['icd_name_en'].config('keywords')['error']['string_max'],

            'icd_name_common.string'      => config('keywords')['icd']['icd_name_common'].config('keywords')['error']['string'],
            'icd_name_common.max'         => config('keywords')['icd']['icd_name_common'].config('keywords')['error']['string_max'],

            'icd_group_id.integer'     => config('keywords')['icd']['icd_group_id'].config('keywords')['error']['integer'],
            'icd_group_id.exists'      => config('keywords')['icd']['icd_group_id'].config('keywords')['error']['exists'],

            'attach_icd_codes.string'     => config('keywords')['icd']['attach_icd_codes'].config('keywords')['error']['string'],
            'attach_icd_codes.max'      => config('keywords')['icd']['attach_icd_codes'].config('keywords')['error']['string_max'],


            'age_from.integer'      => config('keywords')['icd']['age_from'].config('keywords')['error']['integer'],
            'age_from.min'         => config('keywords')['icd']['age_from'].config('keywords')['error']['integer_min'],

            'age_to.integer'      => config('keywords')['icd']['age_to'].config('keywords')['error']['integer'],
            'age_to.min'         => config('keywords')['icd']['age_to'].config('keywords')['error']['integer_min'],
            'age_to.gt'         => config('keywords')['icd']['age_to'].config('keywords')['error']['gt'],

            'age_type_id.integer'     => config('keywords')['icd']['age_type_id'].config('keywords')['error']['integer'],
            'age_type_id.exists'      => config('keywords')['icd']['age_type_id'].config('keywords')['error']['exists'],

            'gender_id.integer'     => config('keywords')['icd']['gender_id'].config('keywords')['error']['integer'],
            'gender_id.exists'      => config('keywords')['icd']['gender_id'].config('keywords')['error']['exists'],

            'is_sword.integer'     => config('keywords')['icd']['is_sword'].config('keywords')['error']['integer'],
            'is_sword.in'      => config('keywords')['icd']['is_sword'].config('keywords')['error']['in'],

            'is_subcode.integer'     => config('keywords')['icd']['is_subcode'].config('keywords')['error']['integer'],
            'is_subcode.in'      => config('keywords')['icd']['is_subcode'].config('keywords')['error']['in'],


            'is_latent_tuberculosis.integer'     => config('keywords')['icd']['is_latent_tuberculosis'].config('keywords')['error']['integer'],
            'is_latent_tuberculosis.in'      => config('keywords')['icd']['is_latent_tuberculosis'].config('keywords')['error']['in'],

            'is_cause.integer'     => config('keywords')['icd']['is_cause'].config('keywords')['error']['integer'],
            'is_cause.in'      => config('keywords')['icd']['is_cause'].config('keywords')['error']['in'],

            'is_hein_nds.integer'     => config('keywords')['icd']['is_hein_nds'].config('keywords')['error']['integer'],
            'is_hein_nds.in'      => config('keywords')['icd']['is_hein_nds'].config('keywords')['error']['in'],

            'is_require_cause.integer'     => config('keywords')['icd']['is_require_cause'].config('keywords')['error']['integer'],
            'is_require_cause.in'      => config('keywords')['icd']['is_require_cause'].config('keywords')['error']['in'],

            'is_traditional.integer'     => config('keywords')['icd']['is_traditional'].config('keywords')['error']['integer'],
            'is_traditional.in'      => config('keywords')['icd']['is_traditional'].config('keywords')['error']['in'],

            'unable_for_treatment.integer'     => config('keywords')['icd']['unable_for_treatment'].config('keywords')['error']['integer'],
            'unable_for_treatment.in'      => config('keywords')['icd']['unable_for_treatment'].config('keywords')['error']['in'],

            'do_not_use_hein.integer'     => config('keywords')['icd']['do_not_use_hein'].config('keywords')['error']['integer'],
            'do_not_use_hein.in'      => config('keywords')['icd']['do_not_use_hein'].config('keywords')['error']['in'],

            'is_covid.integer'     => config('keywords')['icd']['is_covid'].config('keywords')['error']['integer'],
            'is_covid.in'      => config('keywords')['icd']['is_covid'].config('keywords')['error']['in'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('attach_icd_codes')) {
            $this->merge([
                'attach_icd_codes_list' => explode(',', $this->attach_icd_codes),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('attach_icd_codes_list') && ($this->attach_icd_codes_list[0] != null)) {
                $icd_code_parent = Icd::where('id',$this->icd)->value('icd_code') ?? '';
                foreach ($this->attach_icd_codes_list as $id) {
                    if ($id == $icd_code_parent) {
                        $validator->errors()->add('attach_icd_codes', 'Không thể nhận ICD đi kèm với mã = ' . $id);
                    }
                    if (!is_string($id) || !Icd::where('icd_code', $id)->where('is_active', 1)->first() ) {
                        $validator->errors()->add('attach_icd_codes', 'ICD đi kèm với mã = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
