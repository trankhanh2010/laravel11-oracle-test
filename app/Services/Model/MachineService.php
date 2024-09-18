<?php

namespace App\Services\Model;

use App\DTOs\MachineDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Machine\InsertMachineIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MachineRepository;

class MachineService 
{
    protected $machineRepository;
    protected $params;
    public function __construct(MachineRepository $machineRepository)
    {
        $this->machineRepository = $machineRepository;
    }
    public function withParams(MachineDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->machineRepository->applyJoins();
            $data = $this->machineRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->machineRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->machineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->machineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['machine'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->machineName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->machineRepository->applyJoins();
                $data = $this->machineRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->machineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->machineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['machine'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->machineName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->machineRepository->applyJoins()
                    ->where('his_machine.id', $id);
                $data = $this->machineRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['machine'], $e);
        }
    }

    public function createMachine($request)
    {
        try {
            $data = $this->machineRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->machineName));
            // Gọi event để thêm index vào elastic
            event(new InsertMachineIndex($data, $this->params->machineName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['machine'], $e);
        }
    }

    public function updateMachine($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->machineRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->machineRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->machineName));
            // Gọi event để thêm index vào elastic
            event(new InsertMachineIndex($data, $this->params->machineName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['machine'], $e);
        }
    }

    public function deleteMachine($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->machineRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->machineRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->machineName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->machineName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['machine'], $e);
        }
    }
}
