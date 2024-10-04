<?php

namespace App\Http\Requests\MedicineLine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateMedicineLineRequest extends FormRequest
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
        if(!is_numeric($this->medicine_line)){
            throw new HttpResponseException(returnIdError($this->medicine_line));
        }
        return [
            'medicine_line_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\MedicineLine')->ignore($this->medicine_line),
                                                ],
            'medicine_line_name' =>        'required|string|max:100',
            'num_order'  => 'nullable|integer',
            'do_not_required_use_form'  => 'nullable|integer|in:0,1',
            'is_active' =>                      'required|integer|in:0,1'

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
