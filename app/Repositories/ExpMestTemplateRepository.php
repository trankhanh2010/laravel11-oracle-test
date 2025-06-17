<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ExpMestTemplate;
use Illuminate\Support\Facades\DB;

class ExpMestTemplateRepository
{
    protected $expMestTemplate;
    public function __construct(ExpMestTemplate $expMestTemplate)
    {
        $this->expMestTemplate = $expMestTemplate;
    }

    public function applyJoins()
    {
        return $this->expMestTemplate
            ->select(
                'his_exp_mest_template.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_template.exp_mest_template_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_exp_mest_template.exp_mest_template_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_template.is_active'), $isActive);
        }
        return $query;
    }
    public function applySelectByLoginnameFilter($query, $currentLoginname)
    {
        if ($currentLoginname != null) {
            $query->where(function ($q) use ($currentLoginname) {
                $q->where(DB::connection('oracle_his')->raw('his_exp_mest_template.creator'), $currentLoginname)
                ->orWhere(DB::connection('oracle_his')->raw('his_exp_mest_template.is_public'), 1);
            });
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_exp_mest_template.' . $key, $item);
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
        return $this->expMestTemplate->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->expMestTemplate::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'exp_mest_template_code' => $request->exp_mest_template_code,
            'exp_mest_template_name' => $request->exp_mest_template_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'exp_mest_template_code' => $request->exp_mest_template_code,
            'exp_mest_template_name' => $request->exp_mest_template_name,
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
            $data = $this->applyJoins()->where('his_exp_mest_template.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_exp_mest_template.id');
            $maxId = $this->applyJoins()->max('his_exp_mest_template.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('exp_mest_template', 'his_exp_mest_template', $startId, $endId, $batchSize);
            }
        }
    }
}