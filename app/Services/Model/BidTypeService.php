<?php

namespace App\Services\Model;

use App\DTOs\BidTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BidType\InsertBidTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BidTypeRepository;
use Illuminate\Support\Facades\Redis;

class BidTypeService
{
    protected $bidTypeRepository;
    protected $params;
    public function __construct(BidTypeRepository $bidTypeRepository)
    {
        $this->bidTypeRepository = $bidTypeRepository;
    }
    public function withParams(BidTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bidTypeRepository->applyJoins();
            $data = $this->bidTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bidTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bidTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bidTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->bidTypeRepository->applyJoins();
        $data = $this->bidTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->bidTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bidTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bidTypeRepository->applyJoins()
            ->where('his_bid_type.id', $id);
        $data = $this->bidTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->bidTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bidTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->bidTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bidTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid_type'], $e);
        }
    }

    public function createBidType($request)
    {
        try {
            $data = $this->bidTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBidTypeIndex($data, $this->params->bidTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bidTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid_type'], $e);
        }
    }

    public function updateBidType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bidTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bidTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBidTypeIndex($data, $this->params->bidTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bidTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid_type'], $e);
        }
    }

    public function deleteBidType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bidTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bidTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bidTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bidTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid_type'], $e);
        }
    }
}
