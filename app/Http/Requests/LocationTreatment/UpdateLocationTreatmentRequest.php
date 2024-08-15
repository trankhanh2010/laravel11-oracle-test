<?php

namespace App\Http\Requests\LocationTreatment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateLocationTreatmentRequest extends FormRequest
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
            'location_store_name' =>      'required|string|max:1000',
            'data_store_id' =>  [
                                    'nullable',
                                    'integer',
                                    Rule::exists('App\Models\HIS\DataStore', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [

            'location_store_name.required'    => config('keywords')['location_store']['location_store_name'].config('keywords')['error']['required'],
            'location_store_name.string'      => config('keywords')['location_store']['location_store_name'].config('keywords')['error']['string'],
            'location_store_name.max'         => config('keywords')['location_store']['location_store_name'].config('keywords')['error']['string_max'],

            'data_store_id.integer'     => config('keywords')['location_store']['data_store_id'].config('keywords')['error']['integer'],
            'data_store_id.exists'      => config('keywords')['location_store']['data_store_id'].config('keywords')['error']['exists'],

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
