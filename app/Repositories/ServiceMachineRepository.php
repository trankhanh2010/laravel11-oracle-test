<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceMachine;
use Illuminate\Support\Facades\DB;

class ServiceMachineRepository
{
    protected $serviceMachine;
    public function __construct(ServiceMachine $serviceMachine)
    {
        $this->serviceMachine = $serviceMachine;
    }

    public function applyJoins()
    {
        return $this->serviceMachine
        ->leftJoin('his_service as service', 'service.id', '=', 'his_service_machine.service_id')
        ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
        ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_service_machine.machine_id')

        ->select(
            'his_service_machine.*',
            'service.service_name',
            'service.service_code',
            'service_type.service_type_name',
            'service_type.service_type_code',
            'machine.machine_name',
            'machine.machine_code',
            'machine.machine_group_code'
        );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query
                ->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('service.service_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('machine.machine_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('machine.machine_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_machine.is_active'), $isActive);
        }
        return $query;
    }
    public function applyServiceIdFilter($query, $serviceId)
    {
        if ($serviceId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_machine.service_id'), $serviceId);
        }
        return $query;
    }
    public function applyMachineIdFilter($query, $machineId)
    {
        if ($machineId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_machine.machine_id'), $machineId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_code', 'service_name'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['machine_code', 'machine_name'])) {
                        $query->orderBy('machine.' . $key, $item);
                    }
                    if (in_array($key, ['service_type_code', 'service_type_name'])) {
                        $query->orderBy('service_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_machine.' . $key, $item);
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
        return $this->serviceMachine->find($id);
    }
    public function getByServiceIdAndMachineIds($serviceId, $machineIds)
    {
        return $this->serviceMachine->where('service_id', $serviceId)->whereIn('machine_id', $machineIds)->get();
    }
    public function getByMachineIdAndServiceIds($machineId, $serviceIds)
    {
        return $this->serviceMachine->whereIn('service_id', $serviceIds)->where('machine_id', $machineId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByServiceId($id)
    {
        $ids = $this->serviceMachine->where('service_id', $id)->pluck('id')->toArray();
        $this->serviceMachine->where('service_id', $id)->delete();
        return $ids;
    }
    public function deleteByMachineId($id)
    {
        $ids = $this->serviceMachine->where('machine_id', $id)->pluck('id')->toArray();
        $this->serviceMachine->where('machine_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_service_machine.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_machine.id');
            $maxId = $this->applyJoins()->max('his_service_machine.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_machine', 'his_service_machine', $startId, $endId, $batchSize);
            }
        }
    }
}
