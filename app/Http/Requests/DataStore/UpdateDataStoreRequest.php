<?php

namespace App\Http\Requests\DataStore;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class UpdateDataStoreRequest extends FormRequest
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
        // Ép kiểu giá trị sang int nếu nó là số, nếu không thì thành 0
        $stored_department_id = is_numeric($this->stored_department_id) ? (int) $this->stored_department_id : 0;
        return [
            'data_store_name' =>            'required|string|max:100',
            'room_type_id'  =>              [
                                                'required',
                                                'integer',
                                                Rule::exists('App\Models\HIS\RoomType', 'id')
                                                ->where(function ($query) {
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                }),
                                            ],
            'parent_id' =>                  [
                                                'nullable',
                                                'integer',
                                                Rule::exists('App\Models\HIS\DataStore', 'id')
                                                ->where(function ($query) {
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                }),
                                                'not_in:'.$this->id,
                                            ],
            'stored_department_id' =>       [
                                                'nullable',
                                                'integer',
                                                Rule::exists('App\Models\HIS\Department', 'id')
                                                ->where(function ($query) {
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                }),
                                            ],
            'stored_room_id' =>             [
                                                'nullable',
                                                'integer',
                                                Rule::exists('App\Models\HIS\Room', 'id')
                                                ->where(function ($query) use ($stored_department_id){
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                                                    ->where(DB::connection('oracle_his')->raw("department_id"), $stored_department_id);
                                                }),
                                            ],
            'treatment_end_type_ids' =>     'nullable|string|max:50',
            'treatment_type_ids' =>         'nullable|string|max:50',
            'is_active' =>                  'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'data_store_name.required'    => config('keywords')['data_store']['data_store_name'].config('keywords')['error']['required'],
            'data_store_name.string'      => config('keywords')['data_store']['data_store_name'].config('keywords')['error']['string'],
            'data_store_name.max'         => config('keywords')['data_store']['data_store_name'].config('keywords')['error']['string_max'],

            'room_type_id.required'    => config('keywords')['data_store']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['data_store']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['data_store']['room_type_id'].config('keywords')['error']['exists'],  

            'parent_id.integer'     => config('keywords')['data_store']['parent_id'].config('keywords')['error']['integer'],
            'parent_id.exists'      => config('keywords')['data_store']['parent_id'].config('keywords')['error']['exists'], 
            'parent_id.not_in'      => config('keywords')['error']['parent_not_in_id'],

            'stored_department_id.integer'     => config('keywords')['data_store']['stored_department_id'].config('keywords')['error']['integer'],
            'stored_department_id.exists'      => config('keywords')['data_store']['stored_department_id'].config('keywords')['error']['exists'], 

            'stored_room_id.integer'     => config('keywords')['data_store']['stored_room_id'].config('keywords')['error']['integer'],
            'stored_room_id.exists'      => config('keywords')['data_store']['stored_room_id'].config('keywords')['error']['exists'], 

            'treatment_end_type_ids.string'      => config('keywords')['data_store']['treatment_end_type_ids'].config('keywords')['error']['string'],
            'treatment_end_type_ids.max'         => config('keywords')['data_store']['treatment_end_type_ids'].config('keywords')['error']['string_max'],

            'treatment_type_ids.string'      => config('keywords')['data_store']['treatment_type_ids'].config('keywords')['error']['string'],
            'treatment_type_ids.max'         => config('keywords')['data_store']['treatment_type_ids'].config('keywords')['error']['string_max'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 

        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('treatment_end_type_ids')) {
            $this->merge([
                'treatment_end_type_ids_list' => explode(',', $this->treatment_end_type_ids),
            ]);
        }
        if ($this->has('treatment_type_ids')) {
            $this->merge([
                'treatment_type_ids_list' => explode(',', $this->treatment_type_ids),
            ]);
        }
        
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('treatment_end_type_ids_list') && ($this->treatment_end_type_ids_list[0] != null)) {
                foreach ($this->treatment_end_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\TreatmentEndType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('treatment_end_type_ids', 'Loại kết thúc điều trị với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            //////////
            if ($this->has('treatment_type_ids_list') && ($this->treatment_type_ids_list[0] != null)) {
                foreach ($this->treatment_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\TreatmentType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('treatment_type_ids', 'Diện điều trị với id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
