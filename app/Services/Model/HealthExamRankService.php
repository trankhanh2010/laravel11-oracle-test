<?php

namespace App\Services\Model;

use App\DTOs\HealthExamRankDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\HealthExamRank\InsertHealthExamRankIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\HealthExamRankRepository;
use Illuminate\Support\Facades\Redis;

class HealthExamRankService
{
    protected $healthExamRankRepository;
    protected $params;
    public function __construct(HealthExamRankRepository $healthExamRankRepository)
    {
        $this->healthExamRankRepository = $healthExamRankRepository;
    }
    public function withParams(HealthExamRankDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->healthExamRankRepository->applyJoins();
            $data = $this->healthExamRankRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->healthExamRankRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->healthExamRankRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->healthExamRankRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['health_exam_rank'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->healthExamRankRepository->applyJoins();
        $data = $this->healthExamRankRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->healthExamRankRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->healthExamRankRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->healthExamRankRepository->applyJoins()
            ->where('his_health_exam_rank.id', $id);
        $data = $this->healthExamRankRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->healthExamRankName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->healthExamRankName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['health_exam_rank'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->healthExamRankName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->healthExamRankName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['health_exam_rank'], $e);
        }
    }

    // public function createHealthExamRank($request)
    // {
    //     try {
    //         $data = $this->healthExamRankRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertHealthExamRankIndex($data, $this->params->healthExamRankName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->healthExamRankName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['health_exam_rank'], $e);
    //     }
    // }

    // public function updateHealthExamRank($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->healthExamRankRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->healthExamRankRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertHealthExamRankIndex($data, $this->params->healthExamRankName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->healthExamRankName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['health_exam_rank'], $e);
    //     }
    // }

    // public function deleteHealthExamRank($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->healthExamRankRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->healthExamRankRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->healthExamRankName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->healthExamRankName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['health_exam_rank'], $e);
    //     }
    // }
}
