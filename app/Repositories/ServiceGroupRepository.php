<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceGroup;
use Illuminate\Support\Facades\DB;

class ServiceGroupRepository
{
    protected $serviceGroup;
    public function __construct(ServiceGroup $serviceGroup)
    {
        $this->serviceGroup = $serviceGroup;
    }

    public function applyJoins()
    {
        return $this->serviceGroup
            ->select(
                'his_service_group.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_group.service_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service_group.service_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_group.is_active'), $isActive);
        }
        return $query;
    }
    public function applyTabFilter($query, $param, $currentLoginname = '')
    {
        switch ($param) {
            case 'chiDinhDichVuKyThuat':
               $query
               ->where('is_active', 1)
               ->where(function ($q) use ($currentLoginname) {
                    $q->where('creator', $currentLoginname)
                        ->orWhere('is_public', 1);
                });
                return $query;
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_service_group.' . $key, $item);
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
        return $this->serviceGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceGroup::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'service_group_code' => $request->service_group_code,
            'service_group_name' => $request->service_group_name,
            'is_public'  => $request->is_public,     
            'num_order' => $request->num_order,
            'parent_service_id'  => $request->parent_service_id,
            'description'  => $request->description,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'service_group_code' => $request->service_group_code,
            'service_group_name' => $request->service_group_name,
            'is_public'  => $request->is_public,     
            'num_order' => $request->num_order,
            'parent_service_id'  => $request->parent_service_id,
            'description'  => $request->description,
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
            $data = $this->applyJoins()->where('his_service_group.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_group.id');
            $maxId = $this->applyJoins()->max('his_service_group.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_group', 'his_service_group', $startId, $endId, $batchSize);
            }
        }
    }
}