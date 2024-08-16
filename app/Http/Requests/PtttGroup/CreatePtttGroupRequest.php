<?php

namespace App\Http\Requests\PtttGroup;

use App\Models\HIS\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePtttGroupRequest extends FormRequest
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
            'pttt_group_code' =>      'required|string|max:2|unique:App\Models\HIS\PtttGroup,pttt_group_code',
            'pttt_group_name' =>      'required|string|max:100',
            'num_order' =>            'nullable|integer|unique:App\Models\HIS\PtttGroup,num_order',
            'remuneration' =>         'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'bed_service_type_ids' => 'nullable|string|max:4000',
        ];
    }
    public function messages()
    {
        return [
            'pttt_group_code.required'    => config('keywords')['pttt_group']['pttt_group_code'].config('keywords')['error']['required'],
            'pttt_group_code.string'      => config('keywords')['pttt_group']['pttt_group_code'].config('keywords')['error']['string'],
            'pttt_group_code.max'         => config('keywords')['pttt_group']['pttt_group_code'].config('keywords')['error']['string_max'],
            'pttt_group_code.unique'      => config('keywords')['pttt_group']['pttt_group_code'].config('keywords')['error']['unique'],

            'pttt_group_name.required'    => config('keywords')['pttt_group']['pttt_group_name'].config('keywords')['error']['required'],
            'pttt_group_name.string'      => config('keywords')['pttt_group']['pttt_group_name'].config('keywords')['error']['string'],
            'pttt_group_name.max'         => config('keywords')['pttt_group']['pttt_group_name'].config('keywords')['error']['string_max'],

            'num_order.integer'      => config('keywords')['pttt_group']['num_order'].config('keywords')['error']['integer'],
            'num_order.unique'      => config('keywords')['pttt_group']['num_order'].config('keywords')['error']['unique'],

            'remuneration.numeric'     => config('keywords')['pttt_group']['remuneration'].config('keywords')['error']['numeric'],
            'remuneration.regex'       => config('keywords')['pttt_group']['remuneration'].config('keywords')['error']['regex_19_4'],
            'remuneration.min'         => config('keywords')['pttt_group']['remuneration'].config('keywords')['error']['integer_min'],

            'bed_service_type_ids.string'      => config('keywords')['pttt_group']['bed_service_type_ids'].config('keywords')['error']['string'],
            'bed_service_type_ids.max'         => config('keywords')['pttt_group']['bed_service_type_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('bed_service_type_ids')) {
            $this->merge([
                'bed_service_type_ids_list' => explode(',', $this->bed_service_type_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('bed_service_type_ids_list') && ($this->bed_service_type_ids_list[0] != null)) {
                $service_type_id_GI = ServiceType::where('service_type_code','GI')->value('id');
                foreach ($this->bed_service_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Service::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('bed_service_type_ids', 'Dịch vụ giường với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                    if (!\App\Models\HIS\Service::where('id', $id)->where('is_active', 1)->where('service_type_id',$service_type_id_GI)->first()) {
                        $validator->errors()->add('bed_service_type_ids', 'Dịch vụ giường với id = ' . $id . config('keywords')['error']['not_in_service_type_GI']);
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
