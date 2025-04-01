<?php

namespace App\Services\Model;

use App\DTOs\PackingTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PackingType\InsertPackingTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PackingTypeRepository;
use Illuminate\Support\Facades\Redis;

class PackingTypeService
{
    protected $packingTypeRepository;
    protected $params;
    public function __construct(PackingTypeRepository $packingTypeRepository)
    {
        $this->packingTypeRepository = $packingTypeRepository;
    }
    public function withParams(PackingTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->packingTypeRepository->applyJoins();
            $data = $this->packingTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->packingTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->packingTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->packingTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->packingTypeRepository->applyJoins();
        $data = $this->packingTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->packingTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->packingTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->packingTypeRepository->applyJoins()
            ->where('his_packing_type.id', $id);
        $data = $this->packingTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->packingTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->packingTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->packingTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->packingTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }

    public function createPackingType($request)
    {
        try {
            $data = $this->packingTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPackingTypeIndex($data, $this->params->packingTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packingTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }

    public function updatePackingType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packingTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packingTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPackingTypeIndex($data, $this->params->packingTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packingTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }

    public function deletePackingType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packingTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packingTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->packingTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packingTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }
}
