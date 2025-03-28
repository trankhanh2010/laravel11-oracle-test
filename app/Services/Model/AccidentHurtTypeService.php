<?php

namespace App\Services\Model;

use App\DTOs\AccidentHurtTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AccidentHurtTypeRepository;
use Illuminate\Support\Facades\Redis;

class AccidentHurtTypeService 
{
    protected $accidentHurtTypeRepository;
    protected $params;
    public function __construct( AccidentHurtTypeRepository $accidentHurtTypeRepository)
    {
        $this->accidentHurtTypeRepository = $accidentHurtTypeRepository;
    }
    public function withParams(AccidentHurtTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->accidentHurtTypeRepository->applyJoins();
            $data = $this->accidentHurtTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->accidentHurtTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->accidentHurtTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->accidentHurtTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {

            $cacheKey = $this->params->accidentHurtTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->accidentHurtTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->accidentHurtTypeRepository->applyJoins();
                $data = $this->accidentHurtTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->accidentHurtTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->accidentHurtTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->accidentHurtTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->accidentHurtTypeRepository->applyJoins()
                    ->where('his_accident_hurt_type.id', $id);
                $data = $this->accidentHurtTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }

    public function createAccidentHurtType($request)
    {
        try {
            $data = $this->accidentHurtTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentHurtTypeIndex($data, $this->params->accidentHurtTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentHurtTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }

    public function updateAccidentHurtType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentHurtTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentHurtTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentHurtTypeIndex($data, $this->params->accidentHurtTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentHurtTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }

    public function deleteAccidentHurtType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentHurtTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentHurtTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->accidentHurtTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentHurtTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }
}
