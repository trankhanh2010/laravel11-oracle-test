<?php

namespace App\Services\Model;

use App\DTOs\Phieutdvacsbnc2PhieumauDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Phieutdvacsbnc2Phieumau\InsertPhieutdvacsbnc2PhieumauIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Phieutdvacsbnc2PhieumauRepository;
use Illuminate\Support\Facades\Redis;

class Phieutdvacsbnc2PhieumauService
{
    protected $phieutdvacsbnc2PhieumauRepository;
    protected $params;
    public function __construct(Phieutdvacsbnc2PhieumauRepository $phieutdvacsbnc2PhieumauRepository)
    {
        $this->phieutdvacsbnc2PhieumauRepository = $phieutdvacsbnc2PhieumauRepository;
    }
    public function withParams(Phieutdvacsbnc2PhieumauDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->phieutdvacsbnc2PhieumauRepository->applyJoins();
            $data = $this->phieutdvacsbnc2PhieumauRepository->applyKeywordFilter($data, $this->params->keyword);
            $count = $data->count();
            $data = $this->phieutdvacsbnc2PhieumauRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->phieutdvacsbnc2PhieumauRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['phieutdvacsbnc2_phieumau'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->phieutdvacsbnc2PhieumauRepository->applyJoins();
        $count = $data->count();
        $data = $this->phieutdvacsbnc2PhieumauRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->phieutdvacsbnc2PhieumauRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->phieutdvacsbnc2PhieumauRepository->applyJoins()
            ->where('phieutdvacsbnc2_phieumau.id', $id);
        $data = $this->phieutdvacsbnc2PhieumauRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->phieutdvacsbnc2PhieumauName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->phieutdvacsbnc2PhieumauName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['phieutdvacsbnc2_phieumau'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->phieutdvacsbnc2PhieumauName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->phieutdvacsbnc2PhieumauName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['phieutdvacsbnc2_phieumau'], $e);
        }
    }
}
