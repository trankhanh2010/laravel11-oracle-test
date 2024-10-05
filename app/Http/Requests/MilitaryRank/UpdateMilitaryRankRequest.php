<?php

namespace App\Http\Requests\MilitaryRank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateMilitaryRankRequest extends FormRequest
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
        if(!is_numeric($this->military_rank)){
            throw new HttpResponseException(returnIdError($this->military_rank));
        }
        return [
            'military_rank_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:6',
                                                    Rule::unique('App\Models\HIS\MilitaryRank')->ignore($this->military_rank),
                                                ],
            'military_rank_name' =>        'required|string|max:100',
            'num_order' => 'nullable|integer',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'military_rank_code.required'    => config('keywords')['military_rank']['military_rank_code'].config('keywords')['error']['required'],
            'military_rank_code.string'      => config('keywords')['military_rank']['military_rank_code'].config('keywords')['error']['string'],
            'military_rank_code.max'         => config('keywords')['military_rank']['military_rank_code'].config('keywords')['error']['string_max'],
            'military_rank_code.unique'      => config('keywords')['military_rank']['military_rank_code'].config('keywords')['error']['unique'],

            'military_rank_name.required'    => config('keywords')['military_rank']['military_rank_name'].config('keywords')['error']['required'],
            'military_rank_name.string'      => config('keywords')['military_rank']['military_rank_name'].config('keywords')['error']['string'],
            'military_rank_name.max'         => config('keywords')['military_rank']['military_rank_name'].config('keywords')['error']['string_max'],
            'military_rank_name.unique'      => config('keywords')['military_rank']['military_rank_name'].config('keywords')['error']['unique'],

            'num_order.integer'      => config('keywords')['military_rank']['num_order'].config('keywords')['error']['integer'],

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
