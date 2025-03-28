<?php

namespace App\Services\Model;

use App\DTOs\AtcGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AtcGroup\InsertAtcGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AtcGroupRepository;
use Illuminate\Support\Facades\Redis;

class AtcGroupService 
{
    protected $atcGroupRepository;
    protected $params;
    public function __construct(AtcGroupRepository $atcGroupRepository)
    {
        $this->atcGroupRepository = $atcGroupRepository;
    }
    public function withParams(AtcGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->atcGroupRepository->applyJoins();
            $data = $this->atcGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->atcGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->atcGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->atcGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->atcGroupName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->atcGroupName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                 $data = $this->atcGroupRepository->applyJoins();
                $data = $this->atcGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->atcGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->atcGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->atcGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->atcGroupRepository->applyJoins()
                    ->where('his_atc_group.id', $id);
                $data = $this->atcGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }

    public function createAtcGroup($request)
    {
        try {
            $data = $this->atcGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertAtcGroupIndex($data, $this->params->atcGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->atcGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }

    public function updateAtcGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->atcGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->atcGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertAtcGroupIndex($data, $this->params->atcGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->atcGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }

    public function deleteAtcGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->atcGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->atcGroupRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->atcGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->atcGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }
}
