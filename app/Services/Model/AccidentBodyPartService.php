<?php

namespace App\Services\Model;

use App\DTOs\AccidentBodyPartDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AccidentBodyPartRepository;
use Illuminate\Support\Facades\Redis;

class AccidentBodyPartService
{
    protected $accidentBodyPartRepository;
    protected $params;
    public function __construct(AccidentBodyPartRepository $accidentBodyPartRepository)
    {
        $this->accidentBodyPartRepository = $accidentBodyPartRepository;
    }
    public function withParams(AccidentBodyPartDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->accidentBodyPartRepository->applyJoins();
            $data = $this->accidentBodyPartRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->accidentBodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->accidentBodyPartRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->accidentBodyPartRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->accidentBodyPartRepository->applyJoins();
        $data = $this->accidentBodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->accidentBodyPartRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->accidentBodyPartRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->accidentBodyPartRepository->applyJoins()
            ->where('his_accident_body_part.id', $id);
        $data = $this->accidentBodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->accidentBodyPartName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->accidentBodyPartName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->accidentBodyPartName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->accidentBodyPartName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    public function createAccidentBodyPart($request)
    {
        try {
            $data = $this->accidentBodyPartRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAccidentBodyPartIndex($data, $this->params->accidentBodyPartName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentBodyPartName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    public function updateAccidentBodyPart($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentBodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentBodyPartRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAccidentBodyPartIndex($data, $this->params->accidentBodyPartName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentBodyPartName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    public function deleteAccidentBodyPart($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentBodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentBodyPartRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->accidentBodyPartName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentBodyPartName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }
}
