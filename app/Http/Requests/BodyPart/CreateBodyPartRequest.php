<?php

namespace App\Http\Requests\BodyPart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateBodyPartRequest extends FormRequest
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
            'body_part_code' =>      'required|string|max:10|unique:App\Models\HIS\BodyPart,body_part_code',
            'body_part_name' =>      'required|string|max:200',
        ];
    }
    public function messages()
    {
        return [
            'body_part_code.required'    => config('keywords')['body_part']['body_part_code'].config('keywords')['error']['required'],
            'body_part_code.string'      => config('keywords')['body_part']['body_part_code'].config('keywords')['error']['string'],
            'body_part_code.max'         => config('keywords')['body_part']['body_part_code'].config('keywords')['error']['string_max'],
            'body_part_code.unique'      => config('keywords')['body_part']['body_part_code'].config('keywords')['error']['unique'],

            'body_part_name.required'    => config('keywords')['body_part']['body_part_name'].config('keywords')['error']['required'],
            'body_part_name.string'      => config('keywords')['body_part']['body_part_name'].config('keywords')['error']['string'],
            'body_part_name.max'         => config('keywords')['body_part']['body_part_name'].config('keywords')['error']['string_max'],


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
