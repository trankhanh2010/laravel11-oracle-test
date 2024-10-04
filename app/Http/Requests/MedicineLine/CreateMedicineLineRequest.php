<?php

namespace App\Http\Requests\MedicineLine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateMedicineLineRequest extends FormRequest
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
            'medicine_line_code' =>      'required|string|max:2|unique:App\Models\HIS\MedicineLine,medicine_line_code',
            'medicine_line_name' =>      'required|string|max:100',
            'num_order'  => 'nullable|integer',
            'do_not_required_use_form'  => 'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'medicine_line_code.required'    => config('keywords')['medicine_line']['medicine_line_code'].config('keywords')['error']['required'],
            'medicine_line_code.string'      => config('keywords')['medicine_line']['medicine_line_code'].config('keywords')['error']['string'],
            'medicine_line_code.max'         => config('keywords')['medicine_line']['medicine_line_code'].config('keywords')['error']['string_max'],
            'medicine_line_code.unique'      => config('keywords')['medicine_line']['medicine_line_code'].config('keywords')['error']['unique'],

            'medicine_line_name.required'      => config('keywords')['medicine_line']['medicine_line_name'].config('keywords')['error']['required'],
            'medicine_line_name.string'      => config('keywords')['medicine_line']['medicine_line_name'].config('keywords')['error']['string'],
            'medicine_line_name.max'         => config('keywords')['medicine_line']['medicine_line_name'].config('keywords')['error']['string_max'],
            'medicine_line_name.unique'      => config('keywords')['medicine_line']['medicine_line_name'].config('keywords')['error']['unique'],

            'num_order.integer'      => config('keywords')['medicine_line']['num_order'].config('keywords')['error']['integer'],

            'do_not_required_use_form.integer'     => config('keywords')['medicine_line']['do_not_required_use_form'].config('keywords')['error']['integer'], 
            'do_not_required_use_form.in'          => config('keywords')['medicine_line']['do_not_required_use_form'].config('keywords')['error']['in'], 
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
