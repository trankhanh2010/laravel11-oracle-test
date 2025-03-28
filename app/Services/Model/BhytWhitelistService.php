<?php

namespace App\Services\Model;

use App\DTOs\BhytWhitelistDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BhytWhitelist\InsertBhytWhitelistIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BhytWhitelistRepository;
use Illuminate\Support\Facades\Redis;

class BhytWhitelistService 
{
    protected $bhytWhitelistRepository;
    protected $params;
    public function __construct(BhytWhitelistRepository $bhytWhitelistRepository)
    {
        $this->bhytWhitelistRepository = $bhytWhitelistRepository;
    }
    public function withParams(BhytWhitelistDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bhytWhitelistRepository->applyJoins();
            $data = $this->bhytWhitelistRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bhytWhitelistRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bhytWhitelistRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bhytWhitelistRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_whitelist'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->bhytWhitelistName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->bhytWhitelistName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->bhytWhitelistRepository->applyJoins();
                $data = $this->bhytWhitelistRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bhytWhitelistRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bhytWhitelistRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_whitelist'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bhytWhitelistName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bhytWhitelistRepository->applyJoins()
                    ->where('his_bhyt_whitelist.id', $id);
                $data = $this->bhytWhitelistRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_whitelist'], $e);
        }
    }

    public function createBhytWhitelist($request)
    {
        try {
            $data = $this->bhytWhitelistRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBhytWhitelistIndex($data, $this->params->bhytWhitelistName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytWhitelistName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_whitelist'], $e);
        }
    }

    public function updateBhytWhitelist($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bhytWhitelistRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bhytWhitelistRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBhytWhitelistIndex($data, $this->params->bhytWhitelistName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytWhitelistName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_whitelist'], $e);
        }
    }

    public function deleteBhytWhitelist($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bhytWhitelistRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bhytWhitelistRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bhytWhitelistName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytWhitelistName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_whitelist'], $e);
        }
    }
}
