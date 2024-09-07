<?php

namespace App\Http\Requests\EmotionlessMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateEmotionlessMethodRequest extends FormRequest
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
        if(!is_numeric($this->emotionless_method)){
            throw new HttpResponseException(returnIdError($this->emotionless_method));
        }    
        return [
            'emotionless_method_code' =>        [
                                                'required',
                                                'string',
                                                'max:6',
                                                Rule::unique('App\Models\HIS\EmotionlessMethod')->ignore($this->emotionless_method),
                                            ],
            'emotionless_method_name' =>      'required|string|max:100',
            'is_first' =>                     'nullable|integer|in:0,1',
            'is_second' =>                    'nullable|integer|in:0,1',
            'is_anaesthesia' =>               'nullable|integer|in:0,1',
            'hein_code' =>                    'nullable|string|max:10',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'emotionless_method_code.required'    => config('keywords')['emotionless_method']['emotionless_method_code'].config('keywords')['error']['required'],
            'emotionless_method_code.string'      => config('keywords')['emotionless_method']['emotionless_method_code'].config('keywords')['error']['string'],
            'emotionless_method_code.max'         => config('keywords')['emotionless_method']['emotionless_method_code'].config('keywords')['error']['string_max'],
            'emotionless_method_code.unique'      => config('keywords')['emotionless_method']['emotionless_method_code'].config('keywords')['error']['unique'],

            'emotionless_method_name.required'    => config('keywords')['emotionless_method']['emotionless_method_name'].config('keywords')['error']['required'],
            'emotionless_method_name.string'      => config('keywords')['emotionless_method']['emotionless_method_name'].config('keywords')['error']['string'],
            'emotionless_method_name.max'         => config('keywords')['emotionless_method']['emotionless_method_name'].config('keywords')['error']['string_max'],

            'is_first.integer'     => config('keywords')['emotionless_method']['is_first'].config('keywords')['error']['integer'], 
            'is_first.in'          => config('keywords')['emotionless_method']['is_first'].config('keywords')['error']['in'], 

            'is_second.integer'     => config('keywords')['emotionless_method']['is_second'].config('keywords')['error']['integer'], 
            'is_second.in'          => config('keywords')['emotionless_method']['is_second'].config('keywords')['error']['in'], 

            'is_anaesthesia.integer'     => config('keywords')['emotionless_method']['is_anaesthesia'].config('keywords')['error']['integer'], 
            'is_anaesthesia.in'          => config('keywords')['emotionless_method']['is_anaesthesia'].config('keywords')['error']['in'], 

            'hein_code.string'      => config('keywords')['emotionless_method']['hein_code'].config('keywords')['error']['string'],
            'hein_code.max'         => config('keywords')['emotionless_method']['hein_code'].config('keywords')['error']['string_max'],

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
