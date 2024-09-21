<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateModuleRequest extends FormRequest
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
            'module_link' =>      'required|string|max:200|unique:App\Models\ACS\Module,module_link',
            'module_name' =>      'required|string|max:100',
            'module_group_id' =>  [
                                    'nullable',
                                    'integer',
                                    Rule::exists('App\Models\ACS\ModuleGroup', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_acs')->raw("is_active"), 1);
                                    }),
                                ],     
            'is_anonymous' =>     'nullable|integer|in:1',
            'application_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\ACS\Application', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_acs')->raw("is_active"), 1);
                                    }),
                                ], 
            'icon_link' => 'nullable|string|max:1000',
            'module_url' => 'nullable|string|max:200',
            'video_urls' => 'nullable|string|max:4000',
            'num_order'  => 'nullable|integer',
            'parent_id'  => [
                                'nullable',
                                'integer',
                                Rule::exists('App\Models\ACS\Module', 'id')
                                ->where(function ($query) {
                                    $query = $query
                                    ->where(DB::connection('oracle_acs')->raw("is_active"), 1);
                                }),
                            ],    
            'is_visible'  => 'nullable|integer|in:1'    ,
            'is_not_show_dialog'  => 'nullable|integer|in:1',                                  
         
        ];
    }
    public function messages()
    {
        return [
            'module_link.required'    => config('keywords')['module']['module_link'].config('keywords')['error']['required'],
            'module_link.string'      => config('keywords')['module']['module_link'].config('keywords')['error']['string'],
            'module_link.max'         => config('keywords')['module']['module_link'].config('keywords')['error']['string_max'],
            'module_link.unique'      => config('keywords')['module']['module_link'].config('keywords')['error']['unique'],

            'module_name.string'      => config('keywords')['module']['module_name'].config('keywords')['error']['string'],
            'module_name.max'         => config('keywords')['module']['module_name'].config('keywords')['error']['string_max'],
            'module_name.unique'      => config('keywords')['module']['module_name'].config('keywords')['error']['unique'],

            'is_anonymous.integer'     => config('keywords')['module']['is_anonymous'].config('keywords')['error']['integer'], 
            'is_anonymous.in'          => config('keywords')['module']['is_anonymous'].config('keywords')['error']['in'], 

            'module_group_id.integer'     => config('keywords')['module']['module_group_id'].config('keywords')['error']['integer'],
            'module_group_id.exists'      => config('keywords')['module']['module_group_id'].config('keywords')['error']['exists'],

            'application_id.required'    => config('keywords')['module']['application_id'].config('keywords')['error']['required'],
            'application_id.integer'     => config('keywords')['module']['application_id'].config('keywords')['error']['integer'],
            'application_id.exists'      => config('keywords')['module']['application_id'].config('keywords')['error']['exists'],

            'icon_link.string'      => config('keywords')['module']['icon_link'].config('keywords')['error']['string'],
            'icon_link.max'         => config('keywords')['module']['icon_link'].config('keywords')['error']['string_max'],

            'module_url.string'      => config('keywords')['module']['module_url'].config('keywords')['error']['string'],
            'module_url.max'         => config('keywords')['module']['module_url'].config('keywords')['error']['string_max'],

            'video_urls.string'      => config('keywords')['module']['video_urls'].config('keywords')['error']['string'],
            'video_urls.max'         => config('keywords')['module']['video_urls'].config('keywords')['error']['string_max'],

            'num_order.integer'     => config('keywords')['module']['num_order'].config('keywords')['error']['integer'], 

            'parent_id.integer'     => config('keywords')['module']['parent_id'].config('keywords')['error']['integer'],
            'parent_id.exists'      => config('keywords')['module']['parent_id'].config('keywords')['error']['exists'],

            'is_visible.integer'     => config('keywords')['module']['is_visible'].config('keywords')['error']['integer'], 
            'is_visible.in'          => config('keywords')['module']['is_visible'].config('keywords')['error']['in'], 

            'is_not_show_dialog.integer'     => config('keywords')['module']['is_not_show_dialog'].config('keywords')['error']['integer'], 
            'is_not_show_dialog.in'          => config('keywords')['module']['is_not_show_dialog'].config('keywords')['error']['in'], 
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
 