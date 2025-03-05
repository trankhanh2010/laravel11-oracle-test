<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\DebateDetailVView;
use Illuminate\Support\Facades\DB;

class DebateDetailVViewRepository
{
    protected $debateDetailVView;
    public function __construct(DebateDetailVView $debateDetailVView)
    {
        $this->debateDetailVView = $debateDetailVView;
    }

    public function applyJoins()
    {
        return $this->debateDetailVView
            ->select(
                'v_his_debate_detail.*'
            );
    }
    public function applyWithParam($query)
    {
        return $query->with([
            'debate_invite_users:id,debate_id,loginname,username,execute_role_id,description,comment_doctor,is_participation,is_secretary,is_president', 
            'debate_invite_users.execute_role:id,execute_role_code,execute_role_name,is_surgry,is_subclinical,is_subclinical_result',

            'debate_ekip_users:id,debate_id,loginname,username,execute_role_id,description,department_id',
            'debate_ekip_users.department:id,department_code,department_name',
            'debate_ekip_users.execute_role:id,execute_role_code,execute_role_name,is_surgry,is_subclinical,is_subclinical_result',
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_debate_detail.debate_detail_code'), 'like', '%'. $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('lower(v_his_debate_detail.debate_detail_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_debate_detail.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_debate_detail.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_debate_detail.' . $key, $item);
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
        return $this->debateDetailVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->debateDetailVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'debate_detail_v_view_code' => $request->debate_detail_v_view_code,
    //         'debate_detail_v_view_name' => $request->debate_detail_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'debate_detail_v_view_code' => $request->debate_detail_v_view_code,
    //         'debate_detail_v_view_name' => $request->debate_detail_v_view_name,
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
            $data = $this->applyJoins()->where('v_his_debate_detail.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_debate_detail.id');
            $maxId = $this->applyJoins()->max('v_his_debate_detail.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('debate_detail_v_view', 'v_his_debate_detail', $startId, $endId, $batchSize);
            }
        }
    }
}