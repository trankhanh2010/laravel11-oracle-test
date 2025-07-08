<?php

namespace App\Http\Requests\TextLib;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateTextLibRequest extends FormRequest
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
            'title' =>      'required|string|max:100',
            'content' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    // Nếu content có giá trị thì kiểm tra xem có phải base64 của RTF không
                    if ($value !== null && !$this->isBase64EncodedRtf($value)) {
                        $fail('Nội dung không đúng định dạng RTF base64!');
                    }
                },
            ],
            'hashtag' => [
                'nullable',
                'string',
                'max:1000',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '') {
                        // Phải bắt đầu và kết thúc bằng dấu phẩy, và không chứa khoảng trắng
                        if (!preg_match('/^,(?!,)([^,\s]+,)*$/', $value)) {
                            $fail("Định dạng hashtag không hợp lệ. Phải có dạng ,hashtag1, hoặc ,hashtag1,hashtag2,...");
                        }
                    }
                },
            ],
            'is_public' =>    'required|integer|in:0,1',
            'hot_key' =>      'nullable|string|max:50',
            'is_public_in_department' =>    'required|integer|in:0,1',
        ];
    }
    private function isBase64EncodedRtf(string $input): bool
    {
        // Kiểm tra base64 format
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $input)) {
            return false;
        }

        // Decode và kiểm tra nội dung
        $decoded = base64_decode($input, true);
        if ($decoded === false) {
            return false;
        }

        // Phải bắt đầu bằng {\\rtf hoặc {\rtf
        return preg_match('/^\{\\\\?rtf/', $decoded) === 1;
    }

    public function messages()
    {
        return [
            'title.string'      => config('keywords')['text_lib']['title'] . config('keywords')['error']['string'],
            'title.max'         => config('keywords')['text_lib']['title'] . config('keywords')['error']['string_max'],
            'title.unique'      => config('keywords')['text_lib']['title'] . config('keywords')['error']['unique'],

            'content.string'      => config('keywords')['text_lib']['content'] . config('keywords')['error']['string'],

            'hashtag.string'      => config('keywords')['text_lib']['hashtag'] . config('keywords')['error']['string'],

            'is_public.required'    => config('keywords')['text_lib']['is_public'].config('keywords')['error']['required'],            
            'is_public.integer'     => config('keywords')['text_lib']['is_public'].config('keywords')['error']['integer'], 
            'is_public.in'          => config('keywords')['text_lib']['is_public'].config('keywords')['error']['in'], 

            'hot_key.string'      => config('keywords')['text_lib']['hot_key'] . config('keywords')['error']['string'],

            'is_public_in_department.required'    => config('keywords')['text_lib']['is_public_in_department'].config('keywords')['error']['required'],            
            'is_public_in_department.integer'     => config('keywords')['text_lib']['is_public_in_department'].config('keywords')['error']['integer'], 
            'is_public_in_department.in'          => config('keywords')['text_lib']['is_public_in_department'].config('keywords')['error']['in'], 
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
