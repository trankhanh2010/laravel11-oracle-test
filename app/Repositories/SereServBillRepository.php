<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\SereServ;
use App\Models\HIS\SereServBill;
use Illuminate\Support\Facades\DB;

class SereServBillRepository
{
    protected $sereServBill;
    protected $sereServ;
    public function __construct(
        SereServBill $sereServBill,
        SereServ $sereServ,
        )
    {
        $this->sereServBill = $sereServBill;
        $this->sereServ = $sereServ;
    }

    public function applyJoins()
    {
        return $this->sereServBill
            ->select(
                'his_sere_serv_bill.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_bill.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_bill.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_bill.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyBillIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_bill.bill_id'), $id);
        }
        return $query;
    }
    public function applyBillCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->join('his_transaction', 'his_sere_serv_bill.bill_id', '=', 'his_transaction.id') 
                  ->where('his_transaction.transaction_code', $code);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_sere_serv_bill.' . $key, $item);
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
        return $this->sereServBill->find($id);
    }
    public function create($sereServId, $transaction, $appCreator, $appModifier){
        $sereServ = $this->sereServ->find($sereServId);
        $data = $this->sereServBill::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'sere_serv_id' => $sereServId,
            'bill_id' => $transaction->id,
            'price' => $sereServ->vir_total_patient_price,
            'vat_ratio' => $sereServ->vat_ratio,
            'tdl_treatment_id' => $sereServ->tdl_treatment_id,

            'tdl_bill_type_id' => 1, //1-Thuong;2-Dich vu (Ng Tri Phuong) 
            'tdl_service_req_id' => $sereServ->service_req_id,   
            'tdl_primary_price' => $sereServ->primary_price,   
            'tdl_limit_price' => $sereServ->limit_price,   
            'tdl_amount' => $sereServ->amount,   
            'tdl_price' => $sereServ->price,   
            'tdl_original_price' => $sereServ->original_price,   
            'tdl_hein_price' => $sereServ->hein_price,   
            'tdl_hein_ratio' => $sereServ->hein_ratio,   
            'tdl_hein_limit_price' => $sereServ->hein_limit_price,   
            'tdl_hein_limit_ratio' => $sereServ->hein_limit_ratio,   
            'tdl_hein_normal_price' => $sereServ->hein_normal_price,   
            'tdl_add_price' => $sereServ->add_price,   
            'tdl_overtime_price' => $sereServ->overtime_price,    
            'tdl_discount' => $sereServ->discount,   
            'tdl_vat_ratio' => $sereServ->vat_ratio,   
            'tdl_service_type_id'  => $sereServ->tdl_service_type_id,   
            'tdl_hein_service_type_id' => $sereServ->tdl_hein_service_type_id,   
            'tdl_user_price' => $sereServ->user_price,   
            'tdl_other_source_price' => $sereServ->other_source_price,   
            'tdl_total_hein_price' => $sereServ->vir_total_hein_price,   
            'tdl_total_patient_price' => $sereServ->vir_total_patient_price,   
            'tdl_total_patient_price_bhyt' => $sereServ->vir_total_patient_price_bhyt,   
            'tdl_service_id' => $sereServ->service_id,   
            'tdl_service_code' => $sereServ->tdl_service_code,   
            'tdl_service_name' => $sereServ->tdl_service_name,   
            'tdl_service_unit_id' => $sereServ->tdl_service_unit_id,   
            'tdl_patient_type_id'  => $sereServ->patient_type_id,   
            'tdl_request_department_id' => $sereServ->tdl_request_department_id,   
            'tdl_execute_department_id' => $sereServ->tdl_execute_department_id,   
            'tdl_sere_serv_parent_id'  => $sereServ->tdl_sere_serv_parent_id,   
            'tdl_is_out_parent_fee' => $sereServ->is_out_parent_fee,   
            'tdl_real_price' => $sereServ->vir_price,  // Kiểm tra lại trường tương ứng  
            'tdl_real_patient_price'  => $sereServ->vir_patient_price,   //Kiểm tra lại trường tương ứng
            'tdl_real_hein_price' => $sereServ->vir_hein_price,   //Kiểm tra lại trường tương ứng
            'tdl_primary_patient_type_id' => $sereServ->primary_patient_type_id,   

        ]);
        return $data;
    }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_bill_code' => $request->sere_serv_bill_code,
    //         'sere_serv_bill_name' => $request->sere_serv_bill_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_sere_serv_bill.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_sere_serv_bill.id');
            $maxId = $this->applyJoins()->max('his_sere_serv_bill.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_bill', 'his_sere_serv_bill', $startId, $endId, $batchSize);
            }
        }
    }
}