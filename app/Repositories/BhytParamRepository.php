<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\BHYTParam;
use Illuminate\Support\Facades\DB;

class BhytParamRepository
{
    protected $bhytParam;
    public function __construct(BhytParam $bhytParam)
    {
        $this->bhytParam = $bhytParam;
    }

    public function applyJoins()
    {
        return $this->bhytParam
            ->select(
                'his_bhyt_param.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_bhyt_param.MIN_TOTAL_BY_SALARY'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_bhyt_param.MAX_TOTAL_PACKAGE_BY_SALARY'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_bhyt_param.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_bhyt_param.' . $key, $item);
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
        return $this->bhytParam->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->bhytParam::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'base_salary' => $request->base_salary,
            'min_total_by_salary' => $request->min_total_by_salary,
            'max_total_package_by_salary' => $request->max_total_package_by_salary,
            'second_stent_paid_ratio' => $request->second_stent_paid_ratio,
            'priority' => $request->priority,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'base_salary' => $request->base_salary,
            'min_total_by_salary' => $request->min_total_by_salary,
            'max_total_package_by_salary' => $request->max_total_package_by_salary,
            'second_stent_paid_ratio' => $request->second_stent_paid_ratio,
            'priority' => $request->priority,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
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
            $data = $this->applyJoins()->where('his_bhyt_param.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_bhyt_param.id');
            $maxId = $this->applyJoins()->max('his_bhyt_param.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('bhyt_param', 'his_bhyt_param', $startId, $endId, $batchSize);
            }
        }
    }
}