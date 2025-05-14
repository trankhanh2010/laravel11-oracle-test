<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\ServiceReqLView;
use Illuminate\Support\Facades\DB;

class ServiceReqLViewRepository
{
    protected $serviceReqLView;
    public function __construct(ServiceReqLView $serviceReqLView)
    {
        $this->serviceReqLView = $serviceReqLView;
    }

    public function applyJoins()
    {
        return $this->serviceReqLView
            ->select(
                'l_his_service_req.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.service_req_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyServiceReqSttIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('l_his_service_req.service_req_stt_id'), $ids);
        }
        return $query;
    }
    public function applyNotInServiceReqTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereNotIn(DB::connection('oracle_his')->raw('l_his_service_req.service_req_type_id'), $ids);
        }
        return $query;
    }
    public function applyTdlPatientTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('l_his_service_req.tdl_patient_type_id'), $ids);
        }
        return $query;
    }
    public function applyExecuteRoomIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.execute_room_id'), $id);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $time)
    {
        if ($time !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.intruction_time'), '>=', $time);
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $time)
    {
        if ($time !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.intruction_time'), '<=', $time);
        }
        return $query;
    }
    public function applyHasExecuteFilter($query, $hasExecute)
    {
        if (!$hasExecute) {
            $query->where(DB::connection('oracle_his')->raw('l_his_service_req.is_no_execute'), 1);
        }
        return $query;
    }
    public function applyIsNotKskRequriedAprovalOrIsKskApproveFilter($query, $has)
    {
        if ($has) {
            $query = $query->where(DB::connection('oracle_his')->raw('l_his_service_req.TDL_KSK_IS_REQUIRED_APPROVAL'), null);
            $query = $query->orwhere(DB::connection('oracle_his')->raw('l_his_service_req.TDL_IS_KSK_APPROVE'), 1);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('l_his_service_req.' . $key, $item);
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
        return $this->serviceReqLView->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceReqLView::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'service_req_l_view_code' => $request->service_req_l_view_code,
            'service_req_l_view_name' => $request->service_req_l_view_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'service_req_l_view_code' => $request->service_req_l_view_code,
            'service_req_l_view_name' => $request->service_req_l_view_name,
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
            $data = $this->applyJoins()->where('l_his_service_req.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('l_his_service_req.id');
            $maxId = $this->applyJoins()->max('l_his_service_req.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
    
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_req_l_view', 'l_his_service_req', $startId, $endId, $batchSize);
            }
        }
    }
}