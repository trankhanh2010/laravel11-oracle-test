<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\DepositReqListVView;
use Illuminate\Support\Facades\DB;

class DepositReqListVViewRepository
{
    protected $DepositReqListVView;
    public function __construct(DepositReqListVView $DepositReqListVView)
    {
        $this->DepositReqListVView = $DepositReqListVView;
    }

    public function applyJoins()
    {
        return $this->DepositReqListVView
            ->select();
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(('treatment_id'), $id);
        }
        return $query;
    }
    public function applyDepositReqCodeFilter($query, $code)
    {
        if ($code != null) {
            $query->where(('deposit_req_code'), $code);
        }
        return $query;
    }
    public function applyIsDepositFilter($query, $param)
    {
        if($param !== null){
            if ($param) {
                // có transaction và transaction đó chưa bị hủy/ hoặc bị hủy mà đã được khôi phục
                $query->whereNotNull(('deposit_id'))
                ->where(function ($subQuery) {
                    $subQuery->orWhere('transaction_is_cancel', 0)
                             ->orWhereNull('transaction_is_cancel');
                });
            }else{
                // không có transaction hoặc transaction đã bị hủy
                $query->where(function ($subQuery) {
                    $subQuery->whereNull(('deposit_id'))
                             ->orWhere('transaction_is_cancel', 1);
                });
                
            }
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('' . $key, $item);
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
        return $this->DepositReqListVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->DepositReqListVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'deposit_req_list_v_view_code' => $request->deposit_req_list_v_view_code,
    //         'deposit_req_list_v_view_name' => $request->deposit_req_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'deposit_req_list_v_view_code' => $request->deposit_req_list_v_view_code,
    //         'deposit_req_list_v_view_name' => $request->deposit_req_list_v_view_name,
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
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('deposit_req_list_v_view', 'v_his_deposit_req_list', $startId, $endId, $batchSize);
            }
        }
    }
}