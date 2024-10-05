<?php

namespace App\Http\Requests\MilitaryRank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateMilitaryRankRequest extends FormRequest
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
            'military_rank_code' =>      'required|string|max:6|unique:App\Models\HIS\MilitaryRank,military_rank_code',
            'military_rank_name' =>      'required|string|max:100',
            'num_order' => 'nullable|integer',
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
