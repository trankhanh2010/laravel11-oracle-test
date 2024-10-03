<?php

namespace App\Http\Requests\Medicine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdateMedicineRequest extends FormRequest
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
        if (!is_numeric($this->medicine)) {
            throw new HttpResponseException(returnIdError($this->medicine));
        }
        return [
            'medicine_type_id'  =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\MedicineType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'supplier_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Supplier', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'package_number' =>         'nullable|string|max:100',
            'expired_date' =>           'nullable|integer|regex:/^\d{14}$/',
            'amount' =>                 'required|numeric|regex:/^\d{1,17}(\.\d{1,6})?$/|min:0',
            'imp_source_id'   =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ImpSource', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'imp_time'  =>              'nullable|integer|regex:/^\d{14}$/',
            'imp_price'  =>             'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'imp_vat_ratio'  =>         'required|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0|max:1',
            'internal_price' =>         'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'bid_id'    =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Bid', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'tdl_bid_number' =>         'nullable|string|max:30',
            'tdl_bid_num_order' =>      'nullable|string|max:50',
            'tdl_bid_group_code' =>     'nullable|string|max:4',
            'tdl_bid_package_code'  =>  'nullable|string|max:4',
            'tdl_bid_year' =>           'nullable|string|max:20',
            'medicine_register_number' =>   'nullable|string|max:500',
            'medicine_byt_num_order' => 'nullable|string|max:50',
            'medicine_tcy_num_order' => 'nullable|string|max:20',
            'medicine_is_star_mark' =>  'nullable|integer|in:0,1',
            'is_pregnant' =>            'nullable|integer|in:0,1',
            'is_sale_equal_imp_price' => 'nullable|integer|in:0,1',
            'tdl_service_id'  =>  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'active_ingr_bhyt_code' =>  'nullable|string|max:500',
            'active_ingr_bhyt_name' =>  'nullable|string|max:1000',
            'document_price'  =>        'nullable|integer|min:0',
            'national_name' =>          'nullable|string|max:100',
            'manufacturer_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Manufacturer', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'concentra' =>              'nullable|string|max:1000',
            'tdl_imp_mest_code' =>      'nullable|string|max:12',
            'tdl_imp_mest_sub_code' =>  'nullable|string|max:25',
            'imp_unit_amount'  =>       'nullable|numeric|regex:/^\d{1,17}(\.\d{1,6})?$/|min:0',
            'imp_unit_price' =>         'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'tdl_imp_unit_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ServiceUnit', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'tdl_imp_unit_convert_ratio' => 'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'medical_contract_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicalContract', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'contract_price'  =>        'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'profit_ratio' =>           'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/',
            'packing_type_name'  =>     'nullable|string|max:300',
            'hein_service_bhyt_name' => 'nullable|string|max:1500',
            'active_ingr_bhyt_name1' => 'nullable|string|max:1000',
            'medicine_use_form_id'   =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MedicineUseForm', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'dosage_form' =>            'nullable|string|max:100',
            'tax_ratio' =>              'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/',
            'tdl_bid_extra_code' =>     'nullable|string|max:50',
            'locking_reason'  =>        'nullable|string|max:4000',
            'tt_thau' =>                'nullable|string|max:50',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'medicine_type_id.required'    => config('keywords')['medicine']['medicine_type_id'] . config('keywords')['error']['required'],
            'medicine_type_id.integer'     => config('keywords')['medicine']['medicine_type_id'] . config('keywords')['error']['integer'],
            'medicine_type_id.exists'      => config('keywords')['medicine']['medicine_type_id'] . config('keywords')['error']['exists'],

            'supplier_id.integer'     => config('keywords')['medicine']['supplier_id'] . config('keywords')['error']['integer'],
            'supplier_id.exists'      => config('keywords')['medicine']['supplier_id'] . config('keywords')['error']['exists'],

            'package_number.string'      => config('keywords')['medicine']['package_number'] . config('keywords')['error']['string'],
            'package_number.max'         => config('keywords')['medicine']['package_number'] . config('keywords')['error']['string_max'],

            'expired_date.integer'            => config('keywords')['medicine']['expired_date'] . config('keywords')['error']['integer'],
            'expired_date.regex'              => config('keywords')['medicine']['expired_date'] . config('keywords')['error']['regex_ymdhis'],

            'amount.required'    => config('keywords')['medicine']['amount'] . config('keywords')['error']['required'],
            'amount.numeric'     => config('keywords')['medicine']['amount'] . config('keywords')['error']['numeric'],
            'amount.regex'       => config('keywords')['medicine']['amount'] . config('keywords')['error']['regex_23_6'],
            'amount.min'         => config('keywords')['medicine']['amount'] . config('keywords')['error']['integer_min'],

            'imp_source_id.integer'     => config('keywords')['medicine']['imp_source_id'] . config('keywords')['error']['integer'],
            'imp_source_id.exists'      => config('keywords')['medicine']['imp_source_id'] . config('keywords')['error']['exists'],

            'imp_time.integer'            => config('keywords')['medicine']['imp_time'] . config('keywords')['error']['integer'],
            'imp_time.regex'              => config('keywords')['medicine']['imp_time'] . config('keywords')['error']['regex_ymdhis'],

            'imp_price.required'    => config('keywords')['medicine']['imp_price'] . config('keywords')['error']['required'],
            'imp_price.numeric'     => config('keywords')['medicine']['imp_price'] . config('keywords')['error']['numeric'],
            'imp_price.regex'       => config('keywords')['medicine']['imp_price'] . config('keywords')['error']['regex_19_4'],
            'imp_price.min'         => config('keywords')['medicine']['imp_price'] . config('keywords')['error']['integer_min'],

            'imp_vat_ratio.required'    => config('keywords')['medicine']['imp_vat_ratio'] . config('keywords')['error']['required'],
            'imp_vat_ratio.numeric'     => config('keywords')['medicine']['imp_vat_ratio'] . config('keywords')['error']['numeric'],
            'imp_vat_ratio.regex'       => config('keywords')['medicine']['imp_vat_ratio'] . config('keywords')['error']['regex_19_4'],
            'imp_vat_ratio.min'         => config('keywords')['medicine']['imp_vat_ratio'] . config('keywords')['error']['integer_min'],
            'imp_vat_ratio.max'         => config('keywords')['medicine']['imp_vat_ratio'] . config('keywords')['error']['integer_max'],

            'internal_price.numeric'     => config('keywords')['medicine']['internal_price'] . config('keywords')['error']['numeric'],
            'internal_price.regex'       => config('keywords')['medicine']['internal_price'] . config('keywords')['error']['regex_19_4'],
            'internal_price.min'         => config('keywords')['medicine']['internal_price'] . config('keywords')['error']['integer_min'],

            'bid_id.integer'     => config('keywords')['medicine']['bid_id'] . config('keywords')['error']['integer'],
            'bid_id.exists'      => config('keywords')['medicine']['bid_id'] . config('keywords')['error']['exists'],

            'tdl_bid_number.string'      => config('keywords')['medicine']['tdl_bid_number'] . config('keywords')['error']['string'],
            'tdl_bid_number.max'         => config('keywords')['medicine']['tdl_bid_number'] . config('keywords')['error']['string_max'],

            'tdl_bid_num_order.string'      => config('keywords')['medicine']['tdl_bid_num_order'] . config('keywords')['error']['string'],
            'tdl_bid_num_order.max'         => config('keywords')['medicine']['tdl_bid_num_order'] . config('keywords')['error']['string_max'],

            'tdl_bid_group_code.string'      => config('keywords')['medicine']['tdl_bid_group_code'] . config('keywords')['error']['string'],
            'tdl_bid_group_code.max'         => config('keywords')['medicine']['tdl_bid_group_code'] . config('keywords')['error']['string_max'],

            'tdl_bid_package_code.string'      => config('keywords')['medicine']['tdl_bid_package_code'] . config('keywords')['error']['string'],
            'tdl_bid_package_code.max'         => config('keywords')['medicine']['tdl_bid_package_code'] . config('keywords')['error']['string_max'],

            'tdl_bid_year.string'      => config('keywords')['medicine']['tdl_bid_year'] . config('keywords')['error']['string'],
            'tdl_bid_year.max'         => config('keywords')['medicine']['tdl_bid_year'] . config('keywords')['error']['string_max'],

            'medicine_register_number.string'      => config('keywords')['medicine']['medicine_register_number'] . config('keywords')['error']['string'],
            'medicine_register_number.max'         => config('keywords')['medicine']['medicine_register_number'] . config('keywords')['error']['string_max'],

            'medicine_byt_num_order.string'      => config('keywords')['medicine']['medicine_byt_num_order'] . config('keywords')['error']['string'],
            'medicine_byt_num_order.max'         => config('keywords')['medicine']['medicine_byt_num_order'] . config('keywords')['error']['string_max'],

            'medicine_tcy_num_order.string'      => config('keywords')['medicine']['medicine_tcy_num_order'] . config('keywords')['error']['string'],
            'medicine_tcy_num_order.max'         => config('keywords')['medicine']['medicine_tcy_num_order'] . config('keywords')['error']['string_max'],

            'medicine_is_star_mark.integer'     => config('keywords')['medicine']['medicine_is_star_mark'] . config('keywords')['error']['integer'],
            'medicine_is_star_mark.in'          => config('keywords')['medicine']['medicine_is_star_mark'] . config('keywords')['error']['in'],

            'is_pregnant.integer'     => config('keywords')['medicine']['is_pregnant'] . config('keywords')['error']['integer'],
            'is_pregnant.in'          => config('keywords')['medicine']['is_pregnant'] . config('keywords')['error']['in'],

            'is_sale_equal_imp_price.integer'     => config('keywords')['medicine']['is_sale_equal_imp_price'] . config('keywords')['error']['integer'],
            'is_sale_equal_imp_price.in'          => config('keywords')['medicine']['is_sale_equal_imp_price'] . config('keywords')['error']['in'],

            'tdl_service_id.required'    => config('keywords')['medicine']['tdl_service_id'] . config('keywords')['error']['required'],
            'tdl_service_id.integer'     => config('keywords')['medicine']['tdl_service_id'] . config('keywords')['error']['integer'],
            'tdl_service_id.exists'      => config('keywords')['medicine']['tdl_service_id'] . config('keywords')['error']['exists'],

            'active_ingr_bhyt_code.string'      => config('keywords')['medicine']['active_ingr_bhyt_code'] . config('keywords')['error']['string'],
            'active_ingr_bhyt_code.max'         => config('keywords')['medicine']['active_ingr_bhyt_code'] . config('keywords')['error']['string_max'],

            'active_ingr_bhyt_name.string'      => config('keywords')['medicine']['active_ingr_bhyt_name'] . config('keywords')['error']['string'],
            'active_ingr_bhyt_name.max'         => config('keywords')['medicine']['active_ingr_bhyt_name'] . config('keywords')['error']['string_max'],

            'document_price.integer'     => config('keywords')['medicine']['document_price'] . config('keywords')['error']['integer'],
            'document_price.min'         => config('keywords')['medicine']['document_price'] . config('keywords')['error']['integer_min'],

            'national_name.string'      => config('keywords')['medicine']['national_name'] . config('keywords')['error']['string'],
            'national_name.max'         => config('keywords')['medicine']['national_name'] . config('keywords')['error']['string_max'],

            'manufacturer_id.integer'     => config('keywords')['medicine']['manufacturer_id'] . config('keywords')['error']['integer'],
            'manufacturer_id.exists'      => config('keywords')['medicine']['manufacturer_id'] . config('keywords')['error']['exists'],

            'concentra.string'      => config('keywords')['medicine']['concentra'] . config('keywords')['error']['string'],
            'concentra.max'         => config('keywords')['medicine']['concentra'] . config('keywords')['error']['string_max'],

            'tdl_imp_mest_code.string'      => config('keywords')['medicine']['tdl_imp_mest_code'] . config('keywords')['error']['string'],
            'tdl_imp_mest_code.max'         => config('keywords')['medicine']['tdl_imp_mest_code'] . config('keywords')['error']['string_max'],

            'tdl_imp_mest_sub_code.string'      => config('keywords')['medicine']['tdl_imp_mest_sub_code'] . config('keywords')['error']['string'],
            'tdl_imp_mest_sub_code.max'         => config('keywords')['medicine']['tdl_imp_mest_sub_code'] . config('keywords')['error']['string_max'],

            'imp_unit_amount.numeric'     => config('keywords')['medicine']['imp_unit_amount'] . config('keywords')['error']['numeric'],
            'imp_unit_amount.regex'       => config('keywords')['medicine']['imp_unit_amount'] . config('keywords')['error']['regex_23_6'],
            'imp_unit_amount.min'         => config('keywords')['medicine']['imp_unit_amount'] . config('keywords')['error']['integer_min'],

            'imp_unit_price.numeric'     => config('keywords')['medicine']['imp_unit_price'] . config('keywords')['error']['numeric'],
            'imp_unit_price.regex'       => config('keywords')['medicine']['imp_unit_price'] . config('keywords')['error']['regex_19_4'],
            'imp_unit_price.min'         => config('keywords')['medicine']['imp_unit_price'] . config('keywords')['error']['integer_min'],

            'tdl_imp_unit_id.integer'     => config('keywords')['medicine']['tdl_imp_unit_id'] . config('keywords')['error']['integer'],
            'tdl_imp_unit_id.exists'      => config('keywords')['medicine']['tdl_imp_unit_id'] . config('keywords')['error']['exists'],

            'tdl_imp_unit_convert_ratio.numeric'     => config('keywords')['medicine']['tdl_imp_unit_convert_ratio'] . config('keywords')['error']['numeric'],
            'tdl_imp_unit_convert_ratio.regex'       => config('keywords')['medicine']['tdl_imp_unit_convert_ratio'] . config('keywords')['error']['regex_19_4'],
            'tdl_imp_unit_convert_ratio.min'         => config('keywords')['medicine']['tdl_imp_unit_convert_ratio'] . config('keywords')['error']['integer_min'],

            'medical_contract_id.integer'     => config('keywords')['medicine']['medical_contract_id'] . config('keywords')['error']['integer'],
            'medical_contract_id.exists'      => config('keywords')['medicine']['medical_contract_id'] . config('keywords')['error']['exists'],

            'contract_price.numeric'     => config('keywords')['medicine']['contract_price'] . config('keywords')['error']['numeric'],
            'contract_price.regex'       => config('keywords')['medicine']['contract_price'] . config('keywords')['error']['regex_19_4'],
            'contract_price.min'         => config('keywords')['medicine']['contract_price'] . config('keywords')['error']['integer_min'],

            'profit_ratio.numeric'     => config('keywords')['medicine']['profit_ratio'] . config('keywords')['error']['numeric'],
            'profit_ratio.regex'       => config('keywords')['medicine']['profit_ratio'] . config('keywords')['error']['regex_19_4'],

            'packing_type_name.string'      => config('keywords')['medicine']['packing_type_name'] . config('keywords')['error']['string'],
            'packing_type_name.max'         => config('keywords')['medicine']['packing_type_name'] . config('keywords')['error']['string_max'],

            'hein_service_bhyt_name.string'      => config('keywords')['medicine']['hein_service_bhyt_name'] . config('keywords')['error']['string'],
            'hein_service_bhyt_name.max'         => config('keywords')['medicine']['hein_service_bhyt_name'] . config('keywords')['error']['string_max'],

            'active_ingr_bhyt_name1.string'      => config('keywords')['medicine']['active_ingr_bhyt_name1'] . config('keywords')['error']['string'],
            'active_ingr_bhyt_name1.max'         => config('keywords')['medicine']['active_ingr_bhyt_name1'] . config('keywords')['error']['string_max'],

            'medicine_use_form_id.integer'     => config('keywords')['medicine']['medicine_use_form_id'] . config('keywords')['error']['integer'],
            'medicine_use_form_id.exists'      => config('keywords')['medicine']['medicine_use_form_id'] . config('keywords')['error']['exists'],

            'dosage_form.string'      => config('keywords')['medicine']['dosage_form'] . config('keywords')['error']['string'],
            'dosage_form.max'         => config('keywords')['medicine']['dosage_form'] . config('keywords')['error']['string_max'],

            'tax_ratio.numeric'     => config('keywords')['medicine']['tax_ratio'] . config('keywords')['error']['numeric'],
            'tax_ratio.regex'       => config('keywords')['medicine']['tax_ratio'] . config('keywords')['error']['regex_19_4'],
            'tax_ratio.min'         => config('keywords')['medicine']['tax_ratio'] . config('keywords')['error']['integer_min'],

            'tdl_bid_extra_code.string'      => config('keywords')['medicine']['tdl_bid_extra_code'] . config('keywords')['error']['string'],
            'tdl_bid_extra_code.max'         => config('keywords')['medicine']['tdl_bid_extra_code'] . config('keywords')['error']['string_max'],

            'locking_reason.string'      => config('keywords')['medicine']['locking_reason'] . config('keywords')['error']['string'],
            'locking_reason.max'         => config('keywords')['medicine']['locking_reason'] . config('keywords')['error']['string_max'],

            'tt_thau.string'      => config('keywords')['medicine']['tt_thau'] . config('keywords')['error']['string'],
            'tt_thau.max'         => config('keywords')['medicine']['tt_thau'] . config('keywords')['error']['string_max'],

            'is_active.required'    => config('keywords')['all']['is_active'] . config('keywords')['error']['required'],
            'is_active.integer'     => config('keywords')['all']['is_active'] . config('keywords')['error']['integer'],
            'is_active.in'          => config('keywords')['all']['is_active'] . config('keywords')['error']['in'],
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
