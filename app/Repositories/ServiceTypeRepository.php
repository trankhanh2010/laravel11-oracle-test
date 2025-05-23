<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceType;
use Illuminate\Support\Facades\DB;

class ServiceTypeRepository
{
    protected $serviceType;
    public function __construct(ServiceType $serviceType)
    {
        $this->serviceType = $serviceType;
    }

    public function applyJoins()
    {
        return $this->serviceType
            ->leftJoin('his_exe_service_module as exe_service_module', 'exe_service_module.id', '=', 'his_service_type.exe_service_module_id')
            ->select(
                'his_service_type.*',
                'exe_service_module.exe_service_module_name',
                'exe_service_module.module_link'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_type.service_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service_type.service_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        if ($param !== null) {
            if ($param == 'CDHA') {
                $query->whereIn(('service_type_code'), ['HA','NS','SA','CN']);
            }
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['exe_service_module_name', 'module_link'])) {
                        $query->orderBy('exe_service_module.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_type.' . $key, $item);
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
        return $this->serviceType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->serviceType::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_type_code'  => $request->service_type_code,      
            'service_type_name'  => $request->service_type_name,      
            'num_order'  => $request->num_order,        
            'exe_service_module_id'  => $request->exe_service_module_id,
            'is_auto_split_req'  => $request->is_auto_split_req, 
            'is_not_display_assign'  => $request->is_not_display_assign,
            'is_split_req_by_sample_type'  => $request->is_split_req_by_sample_type,
            'is_required_sample_type' => $request->is_required_sample_type,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'service_type_code'  => $request->service_type_code,      
            'service_type_name'  => $request->service_type_name,      
            'num_order'  => $request->num_order,        
            'exe_service_module_id'  => $request->exe_service_module_id,
            'is_auto_split_req'  => $request->is_auto_split_req, 
            'is_not_display_assign'  => $request->is_not_display_assign,
            'is_split_req_by_sample_type'  => $request->is_split_req_by_sample_type,
            'is_required_sample_type' => $request->is_required_sample_type,

            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_service_type.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_type.id');
            $maxId = $this->applyJoins()->max('his_service_type.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_type', 'his_service_type', $startId, $endId, $batchSize);
            }
        }
    }
}
