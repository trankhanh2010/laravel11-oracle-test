<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Medicine;
use Illuminate\Support\Facades\DB;

class MedicineRepository
{
    protected $medicine;
    public function __construct(Medicine $medicine)
    {
        $this->medicine = $medicine;
    }

    public function applyJoins()
    {
        return $this->medicine
            ->select(
                'his_medicine.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine.tdl_bid_package_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medicine.tdl_bid_group_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medicine.' . $key, $item);
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
        return $this->medicine->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicine::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'medicine_type_id' => $request->medicine_type_id,
            'supplier_id' => $request->supplier_id,
            'package_number'  => $request->package_number,
            'expired_date' => $request->expired_date,
            'amount' => $request->amount,
            'imp_source_id' => $request->imp_source_id,
            'imp_time' => $request->imp_time,
            'imp_price' => $request->imp_price,
            'imp_vat_ratio' => $request->imp_vat_ratio,
            'internal_price' => $request->internal_price,
            'bid_id'  => $request->bid_id,
            'tdl_bid_number'  => $request->tdl_bid_number,
            'tdl_bid_num_order'  => $request->tdl_bid_num_order,
            'tdl_bid_group_code' => $request->tdl_bid_group_code,
            'tdl_bid_package_code' => $request->tdl_bid_package_code,
            'tdl_bid_year'  => $request->tdl_bid_year,
            'medicine_register_number' => $request->medicine_register_number,
            'medicine_byt_num_order' => $request->medicine_byt_num_order,
            'medicine_tcy_num_order' => $request->medicine_tcy_num_order,
            'medicine_is_star_mark'  => $request->medicine_is_star_mark,
            'is_pregnant'  => $request->is_pregnant,
            'is_sale_equal_imp_price'  => $request->is_sale_equal_imp_price,
            'tdl_service_id'  => $request->tdl_service_id,
            'active_ingr_bhyt_code'  => $request->active_ingr_bhyt_code,
            'active_ingr_bhyt_name'  => $request->active_ingr_bhyt_name,
            'document_price'  => $request->document_price,
            'national_name'  => $request->national_name,
            'manufacturer_id'  => $request->manufacturer_id,
            'concentra'  => $request->concentra,
            'tdl_imp_mest_code'  => $request->tdl_imp_mest_code,
            'tdl_imp_mest_sub_code'  => $request->tdl_imp_mest_sub_code,
            'imp_unit_amount' => $request->imp_unit_amount,
            'imp_unit_price' => $request->imp_unit_price, 
            'tdl_imp_unit_id' => $request->tdl_imp_unit_id,  
            'tdl_imp_unit_convert_ratio'  => $request->tdl_imp_unit_convert_ratio,
            'medical_contract_id'  => $request->medical_contract_id,
            'contract_price'  => $request->contract_price,
            'profit_ratio'  => $request->profit_ratio,
            'packing_type_name'  => $request->packing_type_name, 
            'hein_service_bhyt_name'  => $request->hein_service_bhyt_name,
            'active_ingr_bhyt_name1' => $request->active_ingr_bhyt_name1,
            'medicine_use_form_id'  => $request->medicine_use_form_id,
            'dosage_form'  => $request->dosage_form,
            'tax_ratio'  => $request->tax_ratio,
            'tdl_bid_extra_code'  => $request->tdl_bid_extra_code,     
            'locking_reason'  => $request->locking_reason, 
            'tt_thau'  => $request->tt_thau,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'medicine_type_id' => $request->medicine_type_id,
            'supplier_id' => $request->supplier_id,
            'package_number'  => $request->package_number,
            'expired_date' => $request->expired_date,
            'amount' => $request->amount,
            'imp_source_id' => $request->imp_source_id,
            'imp_time' => $request->imp_time,
            'imp_price' => $request->imp_price,
            'imp_vat_ratio' => $request->imp_vat_ratio,
            'internal_price' => $request->internal_price,
            'bid_id'  => $request->bid_id,
            'tdl_bid_number'  => $request->tdl_bid_number,
            'tdl_bid_num_order'  => $request->tdl_bid_num_order,
            'tdl_bid_group_code' => $request->tdl_bid_group_code,
            'tdl_bid_package_code' => $request->tdl_bid_package_code,
            'tdl_bid_year'  => $request->tdl_bid_year,
            'medicine_register_number' => $request->medicine_register_number,
            'medicine_byt_num_order' => $request->medicine_byt_num_order,
            'medicine_tcy_num_order' => $request->medicine_tcy_num_order,
            'medicine_is_star_mark'  => $request->medicine_is_star_mark,
            'is_pregnant'  => $request->is_pregnant,
            'is_sale_equal_imp_price'  => $request->is_sale_equal_imp_price,
            'tdl_service_id'  => $request->tdl_service_id,
            'active_ingr_bhyt_code'  => $request->active_ingr_bhyt_code,
            'active_ingr_bhyt_name'  => $request->active_ingr_bhyt_name,
            'document_price'  => $request->document_price,
            'national_name'  => $request->national_name,
            'manufacturer_id'  => $request->manufacturer_id,
            'concentra'  => $request->concentra,
            'tdl_imp_mest_code'  => $request->tdl_imp_mest_code,
            'tdl_imp_mest_sub_code'  => $request->tdl_imp_mest_sub_code,
            'imp_unit_amount' => $request->imp_unit_amount,
            'imp_unit_price' => $request->imp_unit_price, 
            'tdl_imp_unit_id' => $request->tdl_imp_unit_id,  
            'tdl_imp_unit_convert_ratio'  => $request->tdl_imp_unit_convert_ratio,
            'medical_contract_id'  => $request->medical_contract_id,
            'contract_price'  => $request->contract_price,
            'profit_ratio'  => $request->profit_ratio,
            'packing_type_name'  => $request->packing_type_name, 
            'hein_service_bhyt_name'  => $request->hein_service_bhyt_name,
            'active_ingr_bhyt_name1' => $request->active_ingr_bhyt_name1,
            'medicine_use_form_id'  => $request->medicine_use_form_id,
            'dosage_form'  => $request->dosage_form,
            'tax_ratio'  => $request->tax_ratio,
            'tdl_bid_extra_code'  => $request->tdl_bid_extra_code,     
            'locking_reason'  => $request->locking_reason, 
            'tt_thau'  => $request->tt_thau,
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
            $data = $this->applyJoins()->where('his_medicine.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_medicine.id');
            $maxId = $this->applyJoins()->max('his_medicine.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('medicine', 'his_medicine', $startId, $endId, $batchSize);
            }
        }
    }
}