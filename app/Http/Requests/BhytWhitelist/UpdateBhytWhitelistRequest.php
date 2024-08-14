<?php

namespace App\Http\Requests\BhytWhitelist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateBhytWhitelistRequest extends FormRequest
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
            'bhyt_whitelist_code' =>        [
                                                'required',
                                                'string',
                                                'max:3',
                                                Rule::unique('App\Models\HIS\BHYTWhitelist')->ignore($this->id),
                                            ],
            'career_id' =>  [                   'nullable',
                                                'integer',
                                                Rule::exists('App\Models\HIS\Career', 'id')
                                                ->where(function ($query) {
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                }),
                                            ], 
            'is_not_check_bhyt' =>          'nullable|integer|in:0,1',
            'is_active' =>                  'required|integer|in:0,1'

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
