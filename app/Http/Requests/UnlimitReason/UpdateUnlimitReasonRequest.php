<?php

namespace App\Http\Requests\UnlimitReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateUnlimitReasonRequest extends FormRequest
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
            'unlimit_reason' => [
                'required',
                'string',
                'max:500',
                Rule::unique('App\Models\HIS\UnlimitReason')->ignore($this->id),
            ],
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'unlimit_reason.required'    => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['required'],
            'unlimit_reason.string'      => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['string'],
            'unlimit_reason.max'         => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['string_max'],
            'unlimit_reason.unique'      => config('keywords')['unlimit_reason']['unlimit_reason'].config('keywords')['error']['unique'],

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
