<?php

namespace App\Http\Requests\ServiceFollow;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateServiceFollowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'service_id' =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'amount' =>    'nullable|numeric|min:0|regex:/^\d{1,17}(\.\d{1,2})?$/',
            'follow_id' =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'conditioned_amount' =>    'required|numeric|min:0|regex:/^\d{1,15}(\.\d{1,4})?$/',
            'treatment_type_ids' =>             'nullable|string|max:100',
            'is_expend' =>               'nullable|integer|in:0,1',
            'add_if_not_assigned' =>               'nullable|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'service_id.required'    => config('keywords')['service_follow']['service_id'] . config('keywords')['error']['required'],
            'service_id.integer'     => config('keywords')['service_follow']['service_id'] . config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['service_follow']['service_id'] . config('keywords')['error']['exists'],

            'amount.numeric'     => config('keywords')['service_follow']['amount'] . config('keywords')['error']['numeric'],
            'amount.min'         => config('keywords')['service_follow']['amount'] . config('keywords')['error']['integer_min'],
            'amount.regex'       => config('keywords')['service_follow']['amount'] . config('keywords')['error']['regex_19_2'],

            'follow_id.required'    => config('keywords')['service_follow']['follow_id'] . config('keywords')['error']['required'],
            'follow_id.integer'     => config('keywords')['service_follow']['follow_id'] . config('keywords')['error']['integer'],
            'follow_id.exists'      => config('keywords')['service_follow']['follow_id'] . config('keywords')['error']['exists'],

            'conditioned_amount.numeric'     => config('keywords')['service_follow']['conditioned_amount'] . config('keywords')['error']['numeric'],
            'conditioned_amount.min'         => config('keywords')['service_follow']['conditioned_amount'] . config('keywords')['error']['integer_min'],
            'conditioned_amount.regex'       => config('keywords')['service_follow']['conditioned_amount'] . config('keywords')['error']['regex_19_4'],
            'conditioned_amount.required'       => config('keywords')['service_follow']['conditioned_amount'] . config('keywords')['error']['required'],

            'treatment_type_ids.string' => config('keywords')['service_follow']['treatment_type_ids'] . config('keywords')['error']['string'],
            'treatment_type_ids.max'    => config('keywords')['service_follow']['treatment_type_ids'] . config('keywords')['error']['string_max'],

            'is_expend.integer'     => config('keywords')['service_follow']['is_expend'] . config('keywords')['error']['integer'],
            'is_expend.in'          => config('keywords')['service_follow']['is_expend'] . config('keywords')['error']['in'],

            'add_if_not_assigned.integer'     => config('keywords')['service_follow']['add_if_not_assigned'] . config('keywords')['error']['integer'],
            'add_if_not_assigned.in'          => config('keywords')['service_follow']['add_if_not_assigned'] . config('keywords')['error']['in'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('treatment_type_ids')) {
            $this->merge([
                'treatment_type_ids_list' => explode(',', $this->treatment_type_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('treatment_type_ids_list') && ($this->treatment_type_ids_list[0] != null)) {
                foreach ($this->treatment_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\TreatmentType::find($id)) {
                        $validator->errors()->add('treatment_type_ids', 'Diện điều trị với id = ' . $id . ' trong danh sách diện điều trị không tồn tại!');
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
