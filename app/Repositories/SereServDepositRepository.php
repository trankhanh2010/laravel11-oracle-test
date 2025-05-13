<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\SereServ;
use App\Models\HIS\SereServDeposit;
use Illuminate\Support\Facades\DB;

class SereServDepositRepository
{
    protected $sereServDeposit;
    protected $sereServ;
    public function __construct(
        SereServDeposit $sereServDeposit,
        SereServ $sereServ,
        )
    {
        $this->sereServDeposit = $sereServDeposit;
        $this->sereServ = $sereServ;
    }

    public function applyJoins()
    {
        return $this->sereServDeposit
            ->select(
                'his_sere_serv_deposit.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDepositIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.deposit_id'), $id);
        }
        return $query;
    }
    public function applyDepositCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->join('his_transaction', 'his_sere_serv_deposit.deposit_id', '=', 'his_transaction.id') 
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
                    $query->orderBy('his_sere_serv_deposit.' . $key, $item);
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
        return $this->sereServDeposit->find($id);
    }
    public function getByDepositId($id)
    {
        return $this->sereServDeposit->where('deposit_id', $id)->get();
    }
    public function create($sereServId, $amountDeposit, $transaction, $appCreator, $appModifier, $isCancel = 0){
        $sereServ = $this->sereServ->find($sereServId);
        $data = $this->sereServDeposit::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'sere_serv_id' => $sereServId,
            'deposit_id' => $transaction->id,
            'amount' => $amountDeposit, // Giá này là sau khi tính là đúng tuyến hay trái tuyến được hưởng BH bao nhiêu + với giá bệnh nhân trả
            'tdl_treatment_id' => $sereServ->tdl_treatment_id,
            'is_cancel' => $isCancel, // Nếu là tạo qr Vietinbank thì truyền vào isCancel =1

            'tdl_service_req_id' => $sereServ->service_req_id,   
            'tdl_service_id' => $sereServ->service_id,
            'tdl_service_code' => $sereServ->tdl_service_code,   
            'tdl_service_name' => $sereServ->tdl_service_name,   
            'tdl_service_type_id'  => $sereServ->tdl_service_type_id,   
            'tdl_service_unit_id' => $sereServ->tdl_service_unit_id,   
            'tdl_patient_type_id'  => $sereServ->patient_type_id,   
            'tdl_hein_service_type_id' => $sereServ->tdl_hein_service_type_id,   
            'tdl_request_department_id' => $sereServ->tdl_request_department_id,   
            'tdl_execute_department_id' => $sereServ->tdl_execute_department_id,  
            'tdl_sere_serv_parent_id'  => $sereServ->tdl_sere_serv_parent_id,   
            'tdl_is_out_parent_fee' => $sereServ->is_out_parent_fee,   
            'tdl_amount' => $sereServ->amount,   
            'tdl_is_expend' => $sereServ->is_expend, 
            'tdl_hein_price' => $sereServ->hein_price,   
            'tdl_hein_limit_price' => $sereServ->hein_limit_price,   
            'tdl_vir_price' => $sereServ->vir_price,
            'tdl_vir_price_no_add_price' => $sereServ->vir_price_no_add_price,
            'tdl_vir_hein_price' => $sereServ->vir_hein_price,
            'tdl_vir_total_price' => $sereServ->vir_total_price,
            'tdl_vir_total_hein_price' => $sereServ->vir_total_hein_price,
            'tdl_vir_total_patient_price' => $sereServ->vir_total_patient_price,

        ]);
        return $data;
    }
    public function updateTransactionVietinBank($data){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => 'MOS_v2',
            'app_modifier' => 'MOS_v2',
            'is_cancel' => 0
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
            $data = $this->applyJoins()->where('his_sere_serv_deposit.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_sere_serv_deposit.id');
            $maxId = $this->applyJoins()->max('his_sere_serv_deposit.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_deposit', 'his_sere_serv_deposit', $startId, $endId, $batchSize);
            }
        }
    }
}