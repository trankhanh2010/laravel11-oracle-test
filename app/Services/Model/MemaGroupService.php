<?php

namespace App\Services\Model;

use App\DTOs\MemaGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MemaGroup\InsertMemaGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MemaGroupRepository;
use Illuminate\Support\Facades\Redis;

class MemaGroupService 
{
    protected $memaGroupRepository;
    protected $params;
    public function __construct(MemaGroupRepository $memaGroupRepository)
    {
        $this->memaGroupRepository = $memaGroupRepository;
    }
    public function withParams(MemaGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->memaGroupRepository->applyJoins();
            $data = $this->memaGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->memaGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->memaGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->memaGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mema_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->memaGroupName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->memaGroupName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->memaGroupRepository->applyJoins();
                $data = $this->memaGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->memaGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->memaGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mema_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->memaGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->memaGroupRepository->applyJoins()
                    ->where('his_mema_group.id', $id);
                $data = $this->memaGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mema_group'], $e);
        }
    }

    public function createMemaGroup($request)
    {
        try {
            $data = $this->memaGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMemaGroupIndex($data, $this->params->memaGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->memaGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mema_group'], $e);
        }
    }

    public function updateMemaGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->memaGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->memaGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMemaGroupIndex($data, $this->params->memaGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->memaGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mema_group'], $e);
        }
    }

    public function deleteMemaGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->memaGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->memaGroupRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->memaGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->memaGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mema_group'], $e);
        }
    }
}
