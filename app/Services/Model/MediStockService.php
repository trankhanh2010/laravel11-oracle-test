<?php

namespace App\Services\Model;

use App\DTOs\MediStockDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediStock\InsertMediStockIndex;
use App\Events\Elastic\DeleteIndex;
use App\Http\Resources\DB\DataResource;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MediStockRepository;
use Illuminate\Support\Facades\Redis;

class MediStockService
{
    protected $mediStockRepository;
    protected $params;
    public function __construct(MediStockRepository $mediStockRepository)
    {
        $this->mediStockRepository = $mediStockRepository;
    }
    public function withParams(MediStockDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function applyResource($data)
    {
        try {
            $data = new DataResource(resource: $data);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['apply_resource'], $e);
        }
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mediStockRepository->applyJoins();
            $data = $this->mediStockRepository->applyWith($data);
            $data = $this->mediStockRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mediStockRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->mediStockRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mediStockRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            $data = $this->applyResource($data);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->mediStockRepository->applyJoins();
        $data = $this->mediStockRepository->applyWith($data);
        $data = $this->mediStockRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->mediStockRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->mediStockRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $data = $this->applyResource($data);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->mediStockRepository->applyJoins()
            ->where('his_medi_stock.id', $id);
        $data = $this->mediStockRepository->applyWith($data);
        $data = $this->mediStockRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        $data = $this->applyResource($data);
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->mediStockName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mediStockName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->mediStockName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mediStockName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }

    public function createMediStock($request)
    {
        try {
            $data = $this->mediStockRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMediStockIndex($data, $this->params->mediStockName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediStockName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }

    public function updateMediStock($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediStockRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediStockRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMediStockIndex($data, $this->params->mediStockName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediStockName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }

    public function deleteMediStock($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediStockRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediStockRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->mediStockName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediStockName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }
}
