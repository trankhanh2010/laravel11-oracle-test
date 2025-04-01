<?php

namespace App\Services\Model;

use App\DTOs\BedDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BedRepository;
use Illuminate\Support\Facades\Redis;

class BedService
{
    protected $bedRepository;
    protected $params;
    public function __construct(BedRepository $bedRepository)
    {
        $this->bedRepository = $bedRepository;
    }
    public function withParams(BedDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bedRepository->applyJoins();
            $data = $this->bedRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bedRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bedRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bedRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->bedRepository->applyJoins();
        $data = $this->bedRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->bedRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bedRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bedRepository->applyJoins()
            ->where('his_bed.id', $id);
        $data = $this->bedRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->bedName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bedName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->bedName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bedName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], $e);
        }
    }

    public function createBed($request)
    {
        try {
            $data = $this->bedRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBedIndex($data, $this->params->bedName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], $e);
        }
    }

    public function updateBed($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBedIndex($data, $this->params->bedName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], $e);
        }
    }

    public function deleteBed($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bedName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], $e);
        }
    }
}
