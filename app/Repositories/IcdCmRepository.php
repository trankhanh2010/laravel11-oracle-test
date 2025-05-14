<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\IcdCm;
use Illuminate\Support\Facades\DB;

class IcdCmRepository
{
    protected $icdCm;
    public function __construct(IcdCm $icdCm)
    {
        $this->icdCm = $icdCm;
    }

    public function applyJoins()
    {
        return $this->icdCm
            ->select(
                'his_icd_cm.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_icd_cm.icd_cm_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_icd_cm.icd_cm_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_icd_cm.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_icd_cm.' . $key, $item);
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
        return $this->icdCm->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->icdCm::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'icd_cm_code' => $request->icd_cm_code,
            'icd_cm_name' => $request->icd_cm_name,
            'icd_cm_chapter_code' => $request->icd_cm_chapter_code,
            'icd_cm_chapter_name' => $request->icd_cm_chapter_name,
            'icd_cm_group_code' => $request->icd_cm_group_code,
            'icd_cm_group_name' => $request->icd_cm_group_name,
            'icd_cm_sub_group_code' => $request->icd_cm_sub_group_code,
            'icd_cm_sub_group_name' => $request->icd_cm_sub_group_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'icd_cm_code' => $request->icd_cm_code,
            'icd_cm_name' => $request->icd_cm_name,
            'icd_cm_chapter_code' => $request->icd_cm_chapter_code,
            'icd_cm_chapter_name' => $request->icd_cm_chapter_name,
            'icd_cm_group_code' => $request->icd_cm_group_code,
            'icd_cm_group_name' => $request->icd_cm_group_name,
            'icd_cm_sub_group_code' => $request->icd_cm_sub_group_code,
            'icd_cm_sub_group_name' => $request->icd_cm_sub_group_name,
            'is_active' => $request->is_active,
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
            $data = $this->applyJoins()->where('his_icd_cm.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_icd_cm.id');
            $maxId = $this->applyJoins()->max('his_icd_cm.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('icd_cm', 'his_icd_cm', $startId, $endId, $batchSize);
            }
        }
    }
}