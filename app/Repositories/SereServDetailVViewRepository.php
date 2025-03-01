<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\SereServDetailVView;
use Illuminate\Support\Facades\DB;

class SereServDetailVViewRepository
{
    protected $sereServDetailVView;
    public function __construct(SereServDetailVView $sereServDetailVView)
    {
        $this->sereServDetailVView = $sereServDetailVView;
    }

    public function applyJoins()
    {
        return $this->sereServDetailVView
            ->select(
                'v_his_sere_serv_detail.*'
            );
    }
    public function applyWithParam($query)
    {
        return $query->with([
            'exp_mest_medicine', // TH thuốc
            'sere_serv_teins', // XN Xét nghiệm
            'sere_serv_exts', // TT thủ thuật, PT phẫu thuật
            'sere_serv_exts.sar_print', // HA hình ảnh, SA siêu âm, CN thăm dò chức năng, NS nội soi
            'sere_serv_pttts', // PT phẫu thuật
            'sere_serv_pttts.pttt_group',
            'sere_serv_pttts.pttt_method',
            'sere_serv_pttts.pttt_condition',
            'sere_serv_pttts.pttt_catastrophe',
            'sere_serv_pttts.pttt_high_tech',
            'sere_serv_pttts.pttt_priority',
            'sere_serv_pttts.pttt_table',
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_detail.sere_serv_detail_code'), 'like', '%'. $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('lower(v_his_sere_serv_detail.sere_serv_detail_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_detail.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_detail.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_sere_serv_detail.' . $key, $item);
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
        return $this->sereServDetailVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->sereServDetailVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'sere_serv_detail_v_view_code' => $request->sere_serv_detail_v_view_code,
    //         'sere_serv_detail_v_view_name' => $request->sere_serv_detail_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_detail_v_view_code' => $request->sere_serv_detail_v_view_code,
    //         'sere_serv_detail_v_view_name' => $request->sere_serv_detail_v_view_name,
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
            $data = $this->applyJoins()->where('v_his_sere_serv_detail.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_sere_serv_detail.id');
            $maxId = $this->applyJoins()->max('v_his_sere_serv_detail.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_detail_v_view', 'v_his_sere_serv_detail', $startId, $endId, $batchSize);
            }
        }
    }
}