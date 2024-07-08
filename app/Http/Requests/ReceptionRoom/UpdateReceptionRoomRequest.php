<?php

namespace App\Http\Requests\ReceptionRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class UpdateReceptionRoomRequest extends FormRequest
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
        return [
            'reception_room_name' =>            'required|string|max:100',
            'area_id' =>                        '|integer|exists:App\Models\HIS\Area,id',
            'room_type_id'  =>                  'required|integer|exists:App\Models\HIS\RoomType,id',
            'patient_type_ids' =>               'nullable|string|max:50',
            'default_cashier_room_id' =>        'nullable|integer|exists:App\Models\HIS\CashierRoom,id',
            'deposit_account_book_id' =>        'nullable|integer|exists:App\Models\HIS\AccountBook,id',
            'screen_saver_module_link' =>       'nullable|string|max:200|exists:App\Models\ACS\Module,module_link',
            'is_pause' =>                       'nullable|integer|in:0,1',
            'is_allow_no_icd' =>                'nullable|integer|in:0,1',
            'is_restrict_execute_room' =>       'nullable|integer|in:0,1',
            'is_active' =>                      'required|integer|in:0,1'


        ];
    }
    public function messages()
    {
        return [
            'reception_room_name.required'    => config('keywords')['reception_room']['reception_room_name'].config('keywords')['error']['required'],
            'reception_room_name.string'      => config('keywords')['reception_room']['reception_room_name'].config('keywords')['error']['string'],
            'reception_room_name.max'         => config('keywords')['reception_room']['reception_room_name'].config('keywords')['error']['string_max'],

            'area_id.integer'     => config('keywords')['reception_room']['area_id'].config('keywords')['error']['integer'],
            'area_id.exists'      => config('keywords')['reception_room']['area_id'].config('keywords')['error']['exists'], 

            'room_type_id.required'    => config('keywords')['reception_room']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['reception_room']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['reception_room']['room_type_id'].config('keywords')['error']['exists'],  

            'patient_type_ids.string'      => config('keywords')['reception_room']['patient_type_ids'].config('keywords')['error']['string'],
            'patient_type_ids.max'         => config('keywords')['reception_room']['patient_type_ids'].config('keywords')['error']['string_max'],

            'default_cashier_room_id.integer'     => config('keywords')['reception_room']['default_cashier_room_id'].config('keywords')['error']['integer'],
            'default_cashier_room_id.exists'      => config('keywords')['reception_room']['default_cashier_room_id'].config('keywords')['error']['exists'],  

            'deposit_account_book_id.integer'     => config('keywords')['reception_room']['deposit_account_book_id'].config('keywords')['error']['integer'],
            'deposit_account_book_id.exists'      => config('keywords')['reception_room']['deposit_account_book_id'].config('keywords')['error']['exists'],  

            'screen_saver_module_link.string'      => config('keywords')['reception_room']['screen_saver_module_link'].config('keywords')['error']['string'],
            'screen_saver_module_link.max'         => config('keywords')['reception_room']['screen_saver_module_link'].config('keywords')['error']['string_max'],
            'screen_saver_module_link.exists'      => config('keywords')['reception_room']['screen_saver_module_link'].config('keywords')['error']['exists'],

            'is_pause.integer'    => config('keywords')['reception_room']['is_pause'].config('keywords')['error']['integer'],
            'is_pause.in'         => config('keywords')['reception_room']['is_pause'].config('keywords')['error']['in'], 

            'is_allow_no_icd.integer'    => config('keywords')['reception_room']['is_allow_no_icd'].config('keywords')['error']['integer'],
            'is_allow_no_icd.in'         => config('keywords')['reception_room']['is_allow_no_icd'].config('keywords')['error']['in'], 

            'is_restrict_execute_room.integer'    => config('keywords')['reception_room']['is_restrict_execute_room'].config('keywords')['error']['integer'],
            'is_restrict_execute_room.in'         => config('keywords')['reception_room']['is_restrict_execute_room'].config('keywords')['error']['in'], 

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('patient_type_ids')) {
            $this->merge([
                'patient_type_ids_list' => explode(',', $this->patient_type_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('patient_type_ids_list') && ($this->patient_type_ids_list[0] != null)) {
                foreach ($this->patient_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::find($id)) {
                        $validator->errors()->add('patient_type_ids', 'Đối tượng thanh toán với id = ' . $id . ' trong danh sách đối tượng thanh toán không tồn tại!');
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
