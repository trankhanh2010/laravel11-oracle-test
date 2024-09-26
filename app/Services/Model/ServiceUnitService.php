<?php

namespace App\Services\Model;

use App\DTOs\ServiceUnitDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceUnit\InsertServiceUnitIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceUnitRepository;

class ServiceUnitService 
{
    protected $serviceUnitRepository;
    protected $params;
    public function __construct(ServiceUnitRepository $serviceUnitRepository)
    {
        $this->serviceUnitRepository = $serviceUnitRepository;
    }
    public function withParams(ServiceUnitDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceUnitRepository->applyJoins();
            $data = $this->serviceUnitRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->serviceUnitName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->serviceUnitRepository->applyJoins();
                $data = $this->serviceUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->serviceUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->serviceUnitName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->serviceUnitRepository->applyJoins()
                    ->where('his_service_unit.id', $id);
                $data = $this->serviceUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }

    public function createServiceUnit($request)
    {
        try {
            $data = $this->serviceUnitRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceUnitName));
            // Gọi event để thêm index vào elastic
            event(new InsertServiceUnitIndex($data, $this->params->serviceUnitName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }

    public function updateServiceUnit($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceUnitRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceUnitName));
            // Gọi event để thêm index vào elastic
            event(new InsertServiceUnitIndex($data, $this->params->serviceUnitName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }

    public function deleteServiceUnit($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceUnitRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceUnitName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceUnitName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }
}
