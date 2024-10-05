<?php 
namespace App\Repositories;

use App\Models\HIS\MedicineType;
use Illuminate\Support\Facades\DB;

class MedicineTypeRepository
{
    protected $medicineType;
    public function __construct(MedicineType $medicineType)
    {
        $this->medicineType = $medicineType;
    }

    public function applyJoins()
    {
        return $this->medicineType
            ->select(
                'his_medicine_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_type.medicine_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medicine_type.medicine_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medicine_type.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->medicineType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicineType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'medicine_type_code' => $request->medicine_type_code,
            'medicine_type_name' => $request->medicine_type_name,
            'service_id' => $request->service_id,
            'parent_id' => $request->parent_id, 
            'is_leaf'  => $request->is_leaf, 
            'num_order' => $request->num_order,     
            'concentra'  => $request->concentra,  
            'active_ingr_bhyt_code'  => $request->active_ingr_bhyt_code,
            'active_ingr_bhyt_name'  => $request->active_ingr_bhyt_name,
            'register_number'  => $request->register_number,
            'packing_type_id_delete' => $request->packing_type_id_delete, 
            'manufacturer_id'  => $request->manufacturer_id, 
            'medicine_use_form_id'  => $request->medicine_use_form_id,
            'medicine_line_id' => $request->medicine_line_id,    
            'medicine_group_id'  => $request->medicine_group_id, 
            'tdl_service_unit_id'  => $request->tdl_service_unit_id,
            'tdl_gender_id'  => $request->tdl_gender_id,
            'national_name' => $request->national_name,
            'tutorial' => $request->tutorial,
            'imp_price' => $request->imp_price,   
            'imp_vat_ratio'  => $request->imp_vat_ratio,
            'internal_price'  => $request->internal_price,
            'alert_max_in_treatment'  => $request->alert_max_in_treatment, 
            'alert_expired_date'  => $request->alert_expired_date,
            'alert_min_in_stock'  => $request->alert_min_in_stock,
            'alert_max_in_prescription' => $request->alert_max_in_prescription,
            'is_stop_imp'  => $request->is_stop_imp,
            'is_star_mark' => $request->is_star_mark,
            'is_allow_odd'  => $request->is_allow_odd,
            'is_allow_export_odd'  => $request->is_allow_export_odd, 
            'is_functional_food'  => $request->is_functional_food,
            'is_require_hsd'  => $request->is_require_hsd,
            'is_sale_equal_imp_price'  => $request->is_sale_equal_imp_price,
            'is_business'  => $request->is_business,
            'is_raw_medicine'  => $request->is_raw_medicine,
            'is_auto_expend' => $request->is_auto_expend,
            'is_vitamin_a'  => $request->is_vitamin_a,
            'is_vaccine'  => $request->is_vaccine, 
            'is_tcmr'  => $request->is_tcmr,
            'is_must_prepare' => $request->is_must_prepare,
            'use_on_day'  => $request->use_on_day,
            'description'  => $request->description, 
            'mema_group_id' => $request->mema_group_id,
            'byt_num_order' => $request->byt_num_order,
            'tcy_num_order' => $request->tcy_num_order,
            'medicine_type_proprietary_name'  => $request->medicine_type_proprietary_name,
            'packing_type_name'  => $request->packing_type_name,
            'rank' => $request->rank,
            'medicine_national_code' => $request->medicine_national_code,
            'is_kidney'  => $request->is_kidney,
            'is_chemical_substance' => $request->is_chemical_substance,
            'last_exp_price'  => $request->last_exp_price,
            'last_exp_vat_ratio'  => $request->last_exp_vat_ratio,
            'contraindication' => $request->contraindication, 
            'last_imp_price'  => $request->last_imp_price,
            'last_imp_vat_ratio'  => $request->last_imp_vat_ratio,
            'atc_codes'  => $request->atc_codes, 
            'last_expired_date'  => $request->last_expired_date,
            'recording_transaction'  => $request->recording_transaction,
            'is_treatment_day_count'  => $request->is_treatment_day_count,
            'allow_missing_pkg_info'  => $request->allow_missing_pkg_info,
            'is_block_max_in_prescription'  => $request->is_block_max_in_prescription,
            'is_oxygen' => $request->is_oxygen, 
            'is_split_compensation'  => $request->is_split_compensation, 
            'storage_condition_id' => $request->storage_condition_id, 
            'contraindication_ids' => $request->contraindication_ids,
            'is_out_hospital' => $request->is_out_hospital,
            'imp_unit_id' => $request->imp_unit_id,
            'imp_unit_convert_ratio'  => $request->imp_unit_convert_ratio,
            'scientific_name'  => $request->scientific_name,
            'preprocessing' => $request->preprocessing,
            'processing'  => $request->processing,
            'used_part' => $request->used_part,
            'dosage_form' => $request->dosage_form,
            'distributed_amount'  => $request->distributed_amount,
            'is_not_treatment_day_count'  => $request->is_not_treatment_day_count,  
            'is_anaesthesia' => $request->is_anaesthesia,
            'vaccine_type_id'  => $request->vaccine_type_id,  
            'quality_standards'  => $request->quality_standards,
            'source_medicine' => $request->source_medicine,
            'is_drug_store'  => $request->is_drug_store,
            'locking_reason' => $request->locking_reason,
            'preprocessing_code'  => $request->preprocessing_code,
            'processing_code' => $request->processing_code,
            'num_order_circulars20' => $request->num_order_circulars20,
            'is_block_max_in_day'  => $request->is_block_max_in_day,
            'alert_max_in_day' => $request->alert_max_in_day,
            'htu_id'  => $request->htu_id,
            'odd_warning_content' => $request->odd_warning_content,
            'is_original_brand_name'  => $request->is_original_brand_name,
            'is_generic'  => $request->is_generic,
            'is_biologic' => $request->is_biologic,
            'atc_group_codes'  => $request->atc_group_codes,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medicine_type_code' => $request->medicine_type_code,
            'medicine_type_name' => $request->medicine_type_name,
            'service_id' => $request->service_id,
            'parent_id' => $request->parent_id, 
            'is_leaf'  => $request->is_leaf, 
            'num_order' => $request->num_order,     
            'concentra'  => $request->concentra,  
            'active_ingr_bhyt_code'  => $request->active_ingr_bhyt_code,
            'active_ingr_bhyt_name'  => $request->active_ingr_bhyt_name,
            'register_number'  => $request->register_number,
            'packing_type_id_delete' => $request->packing_type_id_delete, 
            'manufacturer_id'  => $request->manufacturer_id, 
            'medicine_use_form_id'  => $request->medicine_use_form_id,
            'medicine_line_id' => $request->medicine_line_id,    
            'medicine_group_id'  => $request->medicine_group_id, 
            'tdl_service_unit_id'  => $request->tdl_service_unit_id,
            'tdl_gender_id'  => $request->tdl_gender_id,
            'national_name' => $request->national_name,
            'tutorial' => $request->tutorial,
            'imp_price' => $request->imp_price,   
            'imp_vat_ratio'  => $request->imp_vat_ratio,
            'internal_price'  => $request->internal_price,
            'alert_max_in_treatment'  => $request->alert_max_in_treatment, 
            'alert_expired_date'  => $request->alert_expired_date,
            'alert_min_in_stock'  => $request->alert_min_in_stock,
            'alert_max_in_prescription' => $request->alert_max_in_prescription,
            'is_stop_imp'  => $request->is_stop_imp,
            'is_star_mark' => $request->is_star_mark,
            'is_allow_odd'  => $request->is_allow_odd,
            'is_allow_export_odd'  => $request->is_allow_export_odd, 
            'is_functional_food'  => $request->is_functional_food,
            'is_require_hsd'  => $request->is_require_hsd,
            'is_sale_equal_imp_price'  => $request->is_sale_equal_imp_price,
            'is_business'  => $request->is_business,
            'is_raw_medicine'  => $request->is_raw_medicine,
            'is_auto_expend' => $request->is_auto_expend,
            'is_vitamin_a'  => $request->is_vitamin_a,
            'is_vaccine'  => $request->is_vaccine, 
            'is_tcmr'  => $request->is_tcmr,
            'is_must_prepare' => $request->is_must_prepare,
            'use_on_day'  => $request->use_on_day,
            'description'  => $request->description, 
            'mema_group_id' => $request->mema_group_id,
            'byt_num_order' => $request->byt_num_order,
            'tcy_num_order' => $request->tcy_num_order,
            'medicine_type_proprietary_name'  => $request->medicine_type_proprietary_name,
            'packing_type_name'  => $request->packing_type_name,
            'rank' => $request->rank,
            'medicine_national_code' => $request->medicine_national_code,
            'is_kidney'  => $request->is_kidney,
            'is_chemical_substance' => $request->is_chemical_substance,
            'last_exp_price'  => $request->last_exp_price,
            'last_exp_vat_ratio'  => $request->last_exp_vat_ratio,
            'contraindication' => $request->contraindication, 
            'last_imp_price'  => $request->last_imp_price,
            'last_imp_vat_ratio'  => $request->last_imp_vat_ratio,
            'atc_codes'  => $request->atc_codes, 
            'last_expired_date'  => $request->last_expired_date,
            'recording_transaction'  => $request->recording_transaction,
            'is_treatment_day_count'  => $request->is_treatment_day_count,
            'allow_missing_pkg_info'  => $request->allow_missing_pkg_info,
            'is_block_max_in_prescription'  => $request->is_block_max_in_prescription,
            'is_oxygen' => $request->is_oxygen, 
            'is_split_compensation'  => $request->is_split_compensation, 
            'storage_condition_id' => $request->storage_condition_id, 
            'contraindication_ids' => $request->contraindication_ids,
            'is_out_hospital' => $request->is_out_hospital,
            'imp_unit_id' => $request->imp_unit_id,
            'imp_unit_convert_ratio'  => $request->imp_unit_convert_ratio,
            'scientific_name'  => $request->scientific_name,
            'preprocessing' => $request->preprocessing,
            'processing'  => $request->processing,
            'used_part' => $request->used_part,
            'dosage_form' => $request->dosage_form,
            'distributed_amount'  => $request->distributed_amount,
            'is_not_treatment_day_count'  => $request->is_not_treatment_day_count,  
            'is_anaesthesia' => $request->is_anaesthesia,
            'vaccine_type_id'  => $request->vaccine_type_id,  
            'quality_standards'  => $request->quality_standards,
            'source_medicine' => $request->source_medicine,
            'is_drug_store'  => $request->is_drug_store,
            'locking_reason' => $request->locking_reason,
            'preprocessing_code'  => $request->preprocessing_code,
            'processing_code' => $request->processing_code,
            'num_order_circulars20' => $request->num_order_circulars20,
            'is_block_max_in_day'  => $request->is_block_max_in_day,
            'alert_max_in_day' => $request->alert_max_in_day,
            'htu_id'  => $request->htu_id,
            'odd_warning_content' => $request->odd_warning_content,
            'is_original_brand_name'  => $request->is_original_brand_name,
            'is_generic'  => $request->is_generic,
            'is_biologic' => $request->is_biologic,
            'atc_group_codes'  => $request->atc_group_codes,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_medicine_type.id','=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes(); 
            })->toArray(); 
        }
        return $data;
    }
}