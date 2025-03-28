<?php

namespace App\Services\Model;

use App\DTOs\BhytBlacklistDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BhytBlacklist\InsertBhytBlacklistIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BhytBlacklistRepository;
use Illuminate\Support\Facades\Redis;

class BhytBlacklistService 
{
    protected $bhytBlacklistRepository;
    protected $params;
    public function __construct(BhytBlacklistRepository $bhytBlacklistRepository)
    {
        $this->bhytBlacklistRepository = $bhytBlacklistRepository;
    }
    public function withParams(BhytBlacklistDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bhytBlacklistRepository->applyJoins();
            $data = $this->bhytBlacklistRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bhytBlacklistRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bhytBlacklistRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bhytBlacklistRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_blacklist'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->bhytBlacklistName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->bhytBlacklistName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->bhytBlacklistRepository->applyJoins();
                $data = $this->bhytBlacklistRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bhytBlacklistRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bhytBlacklistRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_blacklist'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bhytBlacklistName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bhytBlacklistRepository->applyJoins()
                    ->where('his_bhyt_blacklist.id', $id);
                $data = $this->bhytBlacklistRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_blacklist'], $e);
        }
    }

    public function createBhytBlacklist($request)
    {
        try {
            $data = $this->bhytBlacklistRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBhytBlacklistIndex($data, $this->params->bhytBlacklistName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytBlacklistName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_blacklist'], $e);
        }
    }

    public function updateBhytBlacklist($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bhytBlacklistRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bhytBlacklistRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBhytBlacklistIndex($data, $this->params->bhytBlacklistName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytBlacklistName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_blacklist'], $e);
        }
    }

    public function deleteBhytBlacklist($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bhytBlacklistRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bhytBlacklistRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bhytBlacklistName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytBlacklistName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_blacklist'], $e);
        }
    }
}
