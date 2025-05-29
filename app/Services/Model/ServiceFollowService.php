<?php

namespace App\Services\Model;

use App\DTOs\ServiceFollowDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceFollow\InsertServiceFollowIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceFollowRepository;
use Illuminate\Support\Facades\Redis;

class ServiceFollowService
{
    protected $serviceFollowRepository;
    protected $params;
    public function __construct(ServiceFollowRepository $serviceFollowRepository)
    {
        $this->serviceFollowRepository = $serviceFollowRepository;
    }
    public function withParams(ServiceFollowDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceFollowRepository->applyJoins();
            $data = $this->serviceFollowRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceFollowRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceFollowRepository->applyServiceIdFilter($data, $this->params->serviceId);
            $count = $data->count();
            $data = $this->serviceFollowRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceFollowRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceFollowRepository->applyJoins();
        $data = $this->serviceFollowRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->serviceFollowRepository->applyServiceIdFilter($data, $this->params->serviceId);
        $count = $data->count();
        $data = $this->serviceFollowRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceFollowRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceFollowRepository->applyJoins()
            ->where('his_service_follow.id', $id);
        $data = $this->serviceFollowRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->serviceFollowName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceFollowName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->serviceFollowName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceFollowName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }

    public function createServiceFollow($request)
    {
        try {
            $data = $this->serviceFollowRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceFollowIndex($data, $this->params->serviceFollowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceFollowName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }

    public function updateServiceFollow($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceFollowRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceFollowRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceFollowIndex($data, $this->params->serviceFollowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceFollowName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }

    public function deleteServiceFollow($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceFollowRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceFollowRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceFollowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceFollowName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }
}
