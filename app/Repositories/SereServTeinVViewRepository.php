<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\SereServTeinVView;
use Illuminate\Support\Facades\DB;

class SereServTeinVViewRepository
{
    protected $sereServTeinVView;
    public function __construct(SereServTeinVView $sereServTeinVView)
    {
        $this->sereServTeinVView = $sereServTeinVView;
    }

    public function applyJoins()
    {
        return $this->sereServTeinVView
            ->select(
                'v_his_sere_serv_tein.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_tein.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_tein.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_tein.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applySereServIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_sere_serv_tein.sere_serv_id'), $ids);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_sere_serv_tein.' . $key, $item);
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
        return $this->sereServTeinVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->sereServTeinVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'sere_serv_tein_v_view_code' => $request->sere_serv_tein_v_view_code,
    //         'sere_serv_tein_v_view_name' => $request->sere_serv_tein_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_tein_v_view_code' => $request->sere_serv_tein_v_view_code,
    //         'sere_serv_tein_v_view_name' => $request->sere_serv_tein_v_view_name,
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
            $data = $this->applyJoins()->where('v_his_sere_serv_tein.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_sere_serv_tein.id');
            $maxId = $this->applyJoins()->max('v_his_sere_serv_tein.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_tein_v_view', 'v_his_sere_serv_tein', $startId, $endId, $batchSize);
            }
        }
    }
}