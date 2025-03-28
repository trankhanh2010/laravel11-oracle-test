<?php

namespace App\Services\Model;

use App\DTOs\AccidentLocationDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentLocation\InsertAccidentLocationIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AccidentLocationRepository;
use Illuminate\Support\Facades\Redis;

class AccidentLocationService
{
    protected $accidentLocationRepository;
    protected $params;
    public function __construct(AccidentLocationRepository $accidentLocationRepository)
    {
        $this->accidentLocationRepository = $accidentLocationRepository;
    }
    public function withParams(AccidentLocationDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->accidentLocationRepository->applyJoins();
            $data = $this->accidentLocationRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->accidentLocationRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->accidentLocationRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->accidentLocationRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->accidentLocationName . '_' . $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->accidentLocationName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->accidentLocationRepository->applyJoins();
                $data = $this->accidentLocationRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->accidentLocationRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->accidentLocationRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->accidentLocationName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->accidentLocationRepository->applyJoins()
                    ->where('his_accident_location.id', $id);
                $data = $this->accidentLocationRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }

    public function createAccidentLocation($request)
    {
        try {
            $data = $this->accidentLocationRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAccidentLocationIndex($data, $this->params->accidentLocationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentLocationName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }

    public function updateAccidentLocation($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentLocationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentLocationRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAccidentLocationIndex($data, $this->params->accidentLocationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentLocationName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }

    public function deleteAccidentLocation($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentLocationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentLocationRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->accidentLocationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentLocationName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }
}
