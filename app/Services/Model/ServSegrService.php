<?php

namespace App\Services\Model;

use App\DTOs\ServSegrDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServSegr\InsertServSegrIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServSegrRepository;
use Illuminate\Support\Facades\Redis;

class ServSegrService
{
    protected $servSegrRepository;
    protected $params;
    public function __construct(ServSegrRepository $servSegrRepository)
    {
        $this->servSegrRepository = $servSegrRepository;
    }
    public function withParams(ServSegrDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->servSegrRepository->applyJoins();
            $data = $this->servSegrRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->servSegrRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->servSegrRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->servSegrRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['serv_segr'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->servSegrRepository->applyJoins();
        $data = $this->servSegrRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->servSegrRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->servSegrRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->servSegrRepository->applyJoins()
            ->where('his_serv_segr.id', $id);
        $data = $this->servSegrRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->servSegrName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->servSegrName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['serv_segr'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->servSegrName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->servSegrName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['serv_segr'], $e);
        }
    }
}
