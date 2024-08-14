<?php

namespace App\Http\Requests\BhytWhitelist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateBhytWhitelistRequest extends FormRequest
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
            'bhyt_whitelist_code' =>        'required|string|max:3|unique:App\Models\HIS\BHYTWhitelist,bhyt_whitelist_code',
            'career_id' =>  [                   'nullable',
                                                'integer',
                                                Rule::exists('App\Models\HIS\Career', 'id')
                                                ->where(function ($query) {
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                }),
                                            ], 
            'is_not_check_bhyt' =>          'nullable|integer|in:0,1'
        ];
    }
    public function messages()
    {
        return [
            'bhyt_whitelist_code.required'    => config('keywords')['bhyt_whitelist']['bhyt_whitelist_code'].config('keywords')['error']['required'],
            'bhyt_whitelist_code.string'      => config('keywords')['bhyt_whitelist']['bhyt_whitelist_code'].config('keywords')['error']['string'],
            'bhyt_whitelist_code.max'         => config('keywords')['bhyt_whitelist']['bhyt_whitelist_code'].config('keywords')['error']['string_max'],
            'bhyt_whitelist_code.unique'      => config('keywords')['bhyt_whitelist']['bhyt_whitelist_code'].config('keywords')['error']['unique'],

            'career_id.integer'     => config('keywords')['bhyt_whitelist']['career_id'].config('keywords')['error']['integer'],
            'career_id.exists'      => config('keywords')['bhyt_whitelist']['career_id'].config('keywords')['error']['exists'],

            'is_not_check_bhyt.integer'      => config('keywords')['bhyt_whitelist']['is_not_check_bhyt'].config('keywords')['error']['integer'],
            'is_not_check_bhyt.in'      => config('keywords')['bhyt_whitelist']['is_not_check_bhyt'].config('keywords')['error']['in'],

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
