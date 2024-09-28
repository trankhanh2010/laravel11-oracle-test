<?php

namespace App\Http\Requests\BedBsty;

use App\Models\HIS\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateBedBstyRequest extends FormRequest
{
    protected $id_bed_service_type;
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
        $this->id_bed_service_type = ServiceType::where('service_type_code', 'GI')->value('id');
        return [
            'service_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                    ->where(DB::connection('oracle_his')->raw("service_type_id"), $this->id_bed_service_type);
                }),
            ],
            'bed_ids' => 'nullable|string|max:4000',

            'bed_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Bed', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'service_ids' => 'nullable|string|max:4000',
            'is_priority' => 'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'service_id.integer'     => config('keywords')['bed_bsty']['service_id'].config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['bed_bsty']['service_id'].config('keywords')['error']['exists'] . ' Hoặc'. config('keywords')['error']['not_in_service_type_GI'],

            'bed_ids.string'     => config('keywords')['bed_bsty']['bed_ids'].config('keywords')['error']['string'],
            'bed_ids.max'      => config('keywords')['bed_bsty']['bed_ids'].config('keywords')['error']['string_max'],

            'bed_id.integer'     => config('keywords')['bed_bsty']['bed_id'].config('keywords')['error']['integer'],
            'bed_id.exists'      => config('keywords')['bed_bsty']['bed_id'].config('keywords')['error']['exists'],

            'service_ids.string'     => config('keywords')['bed_bsty']['service_ids'].config('keywords')['error']['string'],
            'service_ids.max'      => config('keywords')['bed_bsty']['service_ids'].config('keywords')['error']['string_max'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('bed_ids')) {
            $this->merge([
                'bed_ids_list' => explode(',', $this->bed_ids),
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
            if (($this->bed_id === null) && ($this->service_id === null)) {
                $validator->errors()->add('bed_id',config('keywords')['bed_bsty']['service_id'].' và '.config('keywords')['bed_bsty']['bed_id'].' không thể cùng trống!');
                $validator->errors()->add('service_id',config('keywords')['bed_bsty']['service_id'].' và '.config('keywords')['bed_bsty']['bed_id'].' không thể cùng trống!');
            }
            if (($this->bed_id !== null) && ($this->service_id !== null)) {
                $validator->errors()->add('bed_id',config('keywords')['bed_bsty']['service_id'].' và '.config('keywords')['bed_bsty']['bed_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('service_id',config('keywords')['bed_bsty']['service_id'].' và '.config('keywords')['bed_bsty']['bed_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('bed_ids_list') && ($this->bed_ids_list[0] != null)) {
                foreach ($this->bed_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Bed::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('bed_ids', 'Giường với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('service_ids_list') && ($this->service_ids_list[0] != null)) {
                foreach ($this->service_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Service::where('id', $id)->where('is_active', 1)->where('service_type_id', $this->id_bed_service_type)->first()) {
                        $validator->errors()->add('service_ids', 'Dịch vụ giường với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list'] . ' Hoặc'. config('keywords')['error']['not_in_service_type_GI']);
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
