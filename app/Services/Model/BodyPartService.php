<?php

namespace App\Services\Model;

use App\DTOs\BodyPartDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BodyPart\InsertBodyPartIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BodyPartRepository;
use Illuminate\Support\Facades\Redis;

class BodyPartService
{
    protected $bodyPartRepository;
    protected $params;
    public function __construct(BodyPartRepository $bodyPartRepository)
    {
        $this->bodyPartRepository = $bodyPartRepository;
    }
    public function withParams(BodyPartDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bodyPartRepository->applyJoins();
            $data = $this->bodyPartRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bodyPartRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bodyPartRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->bodyPartRepository->applyJoins();
        $data = $this->bodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->bodyPartRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bodyPartRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bodyPartRepository->applyJoins()
            ->where('his_body_part.id', $id);
        $data = $this->bodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->bodyPartName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bodyPartName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->bodyPartName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bodyPartName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }

    public function createBodyPart($request)
    {
        try {
            $data = $this->bodyPartRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBodyPartIndex($data, $this->params->bodyPartName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bodyPartName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }

    public function updateBodyPart($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bodyPartRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBodyPartIndex($data, $this->params->bodyPartName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bodyPartName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }

    public function deleteBodyPart($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bodyPartRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bodyPartName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bodyPartName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }
}
