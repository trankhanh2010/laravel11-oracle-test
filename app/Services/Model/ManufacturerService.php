<?php

namespace App\Services\Model;

use App\DTOs\ManufacturerDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Manufacturer\InsertManufacturerIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ManufacturerRepository;
use Illuminate\Support\Facades\Redis;

class ManufacturerService 
{
    protected $manufacturerRepository;
    protected $params;
    public function __construct(ManufacturerRepository $manufacturerRepository)
    {
        $this->manufacturerRepository = $manufacturerRepository;
    }
    public function withParams(ManufacturerDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->manufacturerRepository->applyJoins();
            $data = $this->manufacturerRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->manufacturerRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->manufacturerRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->manufacturerRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['manufacturer'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->manufacturerName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->manufacturerName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->manufacturerRepository->applyJoins();
                $data = $this->manufacturerRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->manufacturerRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->manufacturerRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['manufacturer'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->manufacturerName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->manufacturerRepository->applyJoins()
                    ->where('his_manufacturer.id', $id);
                $data = $this->manufacturerRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['manufacturer'], $e);
        }
    }

    public function createManufacturer($request)
    {
        try {
            $data = $this->manufacturerRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertManufacturerIndex($data, $this->params->manufacturerName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->manufacturerName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['manufacturer'], $e);
        }
    }

    public function updateManufacturer($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->manufacturerRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->manufacturerRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertManufacturerIndex($data, $this->params->manufacturerName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->manufacturerName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['manufacturer'], $e);
        }
    }

    public function deleteManufacturer($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->manufacturerRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->manufacturerRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->manufacturerName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->manufacturerName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['manufacturer'], $e);
        }
    }
}
