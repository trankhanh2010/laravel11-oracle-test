<?php

namespace App\Http\Requests\Bed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateBedRequest extends FormRequest
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
        if(!is_numeric($this->bed)){
            throw new HttpResponseException(returnIdError($this->bed));
        }
        return [
            'bed_code' =>        [
                                    'required',
                                    'string',
                                    'max:10',
                                    Rule::unique('App\Models\HIS\Bed')->ignore($this->bed),
                                ],            
            'bed_name' =>      'required|string|max:200',
            'bed_type_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\BedType', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'bed_room_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\BedRoom', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'max_capacity' =>       [
                                        'nullable',
                                        'integer',
                                        'min:0',
                                        'required_if:is_bed_stretcher,1', // Trường b bắt buộc nếu a bằng 1
                                        function ($attribute, $value, $fail) {
                                            if (($this->is_bed_stretcher == 1) && ($this->max_capacity != 1)) {
                                                $fail(config('keywords')['bed']['max_capacity'].' phải bằng 1 nếu '.config('keywords')['bed']['is_bed_stretcher']. ' được chọn!');
                                            }
                                        },
                                    ],
            'is_bed_stretcher' =>   'nullable|integer|in:0,1',
            'is_active' =>                      'required|integer|in:0,1'
        ];
    }
    public function messages()
    {
        return [
            'bed_code.required'    => config('keywords')['bed']['bed_code'].config('keywords')['error']['required'],
            'bed_code.string'      => config('keywords')['bed']['bed_code'].config('keywords')['error']['string'],
            'bed_code.max'         => config('keywords')['bed']['bed_code'].config('keywords')['error']['string_max'],
            'bed_code.unique'      => config('keywords')['bed']['bed_code'].config('keywords')['error']['unique'],

            'bed_name.required'    => config('keywords')['bed']['bed_name'].config('keywords')['error']['required'],
            'bed_name.string'      => config('keywords')['bed']['bed_name'].config('keywords')['error']['string'],
            'bed_name.max'         => config('keywords')['bed']['bed_name'].config('keywords')['error']['string_max'],
            'bed_name.unique'      => config('keywords')['bed']['bed_name'].config('keywords')['error']['unique'],

            'bed_type_id.required'    => config('keywords')['bed']['bed_type_id'].config('keywords')['error']['required'],
            'bed_type_id.integer'     => config('keywords')['bed']['bed_type_id'].config('keywords')['error']['integer'],
            'bed_type_id.exists'      => config('keywords')['bed']['bed_type_id'].config('keywords')['error']['exists'],

            'bed_room_id.required'    => config('keywords')['bed']['bed_room_id'].config('keywords')['error']['required'],
            'bed_room_id.integer'     => config('keywords')['bed']['bed_room_id'].config('keywords')['error']['integer'],
            'bed_room_id.exists'      => config('keywords')['bed']['bed_room_id'].config('keywords')['error']['exists'],

            'max_capacity.integer'    => config('keywords')['bed']['max_capacity'].config('keywords')['error']['integer'],
            'max_capacity.min'         => config('keywords')['bed']['max_capacity'].config('keywords')['error']['integer_min'],

            'is_bed_stretcher.integer'    => config('keywords')['bed']['is_bed_stretcher'].config('keywords')['error']['integer'],
            'is_bed_stretcher.in'         => config('keywords')['bed']['is_bed_stretcher'].config('keywords')['error']['in'], 

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
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
