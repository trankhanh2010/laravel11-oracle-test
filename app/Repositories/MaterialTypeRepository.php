<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MaterialType;
use Illuminate\Support\Facades\DB;

class MaterialTypeRepository
{
    protected $materialType;
    public function __construct(MaterialType $materialType)
    {
        $this->materialType = $materialType;
    }

    public function applyJoins()
    {
        return $this->materialType
            ->select(
                'his_material_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_material_type.material_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_material_type.material_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_material_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_material_type.' . $key, $item);
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
        return $this->materialType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->materialType::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            
            'material_type_code' => $request->material_type_code,
            'material_type_name' => $request->material_type_name,
            'service_id'  => $request->service_id,       
            'parent_id'  => $request->parent_id,       
            'is_leaf' => $request->is_leaf,       
            'num_order' => $request->num_order,     
            'concentra'  => $request->concentra,
            'packing_type_id_delete'  => $request->packing_type_id_delete,
            'manufacturer_id' => $request->manufacturer_id,     
            'tdl_service_unit_id'  => $request->tdl_service_unit_id,           
            'tdl_gender_id' => $request->tdl_gender_id,
            'national_name' => $request->national_name,    
            'imp_price'  => $request->imp_price,   
            'imp_vat_ratio'  => $request->imp_vat_ratio, 
            'internal_price' => $request->internal_price,
            'alert_expired_date'  => $request->alert_expired_date,
            'alert_min_in_stock'  => $request->alert_min_in_stock,
            'alert_max_in_prescription'  => $request->alert_max_in_prescription,
            'is_chemical_substance' => $request->is_chemical_substance,
            'is_auto_expend'  => $request->is_auto_expend,
            'is_stent'  => $request->is_stent,
            'is_in_ktc_fee'  => $request->is_in_ktc_fee,
            'is_allow_odd' => $request->is_allow_odd,
            'is_allow_export_odd'  => $request->is_allow_export_odd,  
            'is_stop_imp'  => $request->is_stop_imp,
            'is_require_hsd'  => $request->is_require_hsd,
            'is_sale_equal_imp_price'  => $request->is_sale_equal_imp_price,
            'is_business'  => $request->is_business,
            'is_raw_material' => $request->is_raw_material, 
            'is_must_prepare' => $request->is_must_prepare,
            'description'  => $request->description,     
            'mema_group_id'  => $request->mema_group_id,      
            'packing_type_name'  => $request->packing_type_name,
            'is_reusable'  => $request->is_reusable,
            'max_reuse_count'  => $request->max_reuse_count,
            'material_group_bhyt'  => $request->material_group_bhyt,
            'material_type_map_id' => $request->material_type_map_id,
            'last_exp_price'  => $request->last_exp_price,
            'last_exp_vat_ratio'  => $request->last_exp_vat_ratio,
            'last_imp_price'  => $request->last_imp_price,
            'last_imp_vat_ratio'  => $request->last_imp_vat_ratio,
            'film_size_id' => $request->film_size_id,
            'is_film'  => $request->is_film,
            'last_expired_date' => $request->last_expired_date,
            'recording_transaction' => $request->recording_transaction,
            'register_number' => $request->register_number,
            'is_consumable'  => $request->is_consumable,
            'is_out_hospital'  => $request->is_out_hospital, 
            'imp_unit_id' => $request->imp_unit_id,
            'imp_unit_convert_ratio'  => $request->imp_unit_convert_ratio,
            'is_drug_store' => $request->is_drug_store,
            'is_not_show_tracking'  => $request->is_not_show_tracking,
            'locking_reason'  => $request->locking_reason,
            'alert_max_in_day' => $request->alert_max_in_day,
            'model_code'  => $request->model_code,    
            'is_identity_management'  => $request->is_identity_management,
            'is_size_required'  => $request->is_size_required,
            'pricing_max_reuse_count' => $request->pricing_max_reuse_count,
            'reuse_fee' => $request->reuse_fee,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'material_type_code' => $request->material_type_code,
            'material_type_name' => $request->material_type_name,
            'service_id'  => $request->service_id,       
            'parent_id'  => $request->parent_id,       
            'is_leaf' => $request->is_leaf,       
            'num_order' => $request->num_order,     
            'concentra'  => $request->concentra,
            'packing_type_id_delete'  => $request->packing_type_id_delete,
            'manufacturer_id' => $request->manufacturer_id,     
            'tdl_service_unit_id'  => $request->tdl_service_unit_id,           
            'tdl_gender_id' => $request->tdl_gender_id,
            'national_name' => $request->national_name,    
            'imp_price'  => $request->imp_price,   
            'imp_vat_ratio'  => $request->imp_vat_ratio, 
            'internal_price' => $request->internal_price,
            'alert_expired_date'  => $request->alert_expired_date,
            'alert_min_in_stock'  => $request->alert_min_in_stock,
            'alert_max_in_prescription'  => $request->alert_max_in_prescription,
            'is_chemical_substance' => $request->is_chemical_substance,
            'is_auto_expend'  => $request->is_auto_expend,
            'is_stent'  => $request->is_stent,
            'is_in_ktc_fee'  => $request->is_in_ktc_fee,
            'is_allow_odd' => $request->is_allow_odd,
            'is_allow_export_odd'  => $request->is_allow_export_odd,  
            'is_stop_imp'  => $request->is_stop_imp,
            'is_require_hsd'  => $request->is_require_hsd,
            'is_sale_equal_imp_price'  => $request->is_sale_equal_imp_price,
            'is_business'  => $request->is_business,
            'is_raw_material' => $request->is_raw_material, 
            'is_must_prepare' => $request->is_must_prepare,
            'description'  => $request->description,     
            'mema_group_id'  => $request->mema_group_id,      
            'packing_type_name'  => $request->packing_type_name,
            'is_reusable'  => $request->is_reusable,
            'max_reuse_count'  => $request->max_reuse_count,
            'material_group_bhyt'  => $request->material_group_bhyt,
            'material_type_map_id' => $request->material_type_map_id,
            'last_exp_price'  => $request->last_exp_price,
            'last_exp_vat_ratio'  => $request->last_exp_vat_ratio,
            'last_imp_price'  => $request->last_imp_price,
            'last_imp_vat_ratio'  => $request->last_imp_vat_ratio,
            'film_size_id' => $request->film_size_id,
            'is_film'  => $request->is_film,
            'last_expired_date' => $request->last_expired_date,
            'recording_transaction' => $request->recording_transaction,
            'register_number' => $request->register_number,
            'is_consumable'  => $request->is_consumable,
            'is_out_hospital'  => $request->is_out_hospital, 
            'imp_unit_id' => $request->imp_unit_id,
            'imp_unit_convert_ratio'  => $request->imp_unit_convert_ratio,
            'is_drug_store' => $request->is_drug_store,
            'is_not_show_tracking'  => $request->is_not_show_tracking,
            'locking_reason'  => $request->locking_reason,
            'alert_max_in_day' => $request->alert_max_in_day,
            'model_code'  => $request->model_code,    
            'is_identity_management'  => $request->is_identity_management,
            'is_size_required'  => $request->is_size_required,
            'pricing_max_reuse_count' => $request->pricing_max_reuse_count,
            'reuse_fee' => $request->reuse_fee,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_material_type.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_material_type.id');
            $maxId = $this->applyJoins()->max('his_material_type.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('material_type', 'his_material_type', $startId, $endId, $batchSize);
            }
        }
    }
}