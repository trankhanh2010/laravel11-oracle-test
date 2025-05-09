<?php

namespace App\Services\Model;

use App\DTOs\MediRecordTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediRecordType\InsertMediRecordTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MediRecordTypeRepository;
use Illuminate\Support\Facades\Redis;

class MediRecordTypeService
{
    protected $mediRecordTypeRepository;
    protected $params;
    public function __construct(MediRecordTypeRepository $mediRecordTypeRepository)
    {
        $this->mediRecordTypeRepository = $mediRecordTypeRepository;
    }
    public function withParams(MediRecordTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mediRecordTypeRepository->applyJoins();
            $data = $this->mediRecordTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mediRecordTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->mediRecordTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mediRecordTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_record_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->mediRecordTypeRepository->applyJoins();
        $data = $this->mediRecordTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->mediRecordTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->mediRecordTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->mediRecordTypeRepository->applyJoins()
            ->where('his_medi_record_type.id', $id);
        $data = $this->mediRecordTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->mediRecordTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mediRecordTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_record_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->mediRecordTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mediRecordTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_record_type'], $e);
        }
    }

    public function createMediRecordType($request)
    {
        try {
            $data = $this->mediRecordTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMediRecordTypeIndex($data, $this->params->mediRecordTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediRecordTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_record_type'], $e);
        }
    }

    public function updateMediRecordType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediRecordTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediRecordTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMediRecordTypeIndex($data, $this->params->mediRecordTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediRecordTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_record_type'], $e);
        }
    }

    public function deleteMediRecordType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediRecordTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediRecordTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->mediRecordTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediRecordTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_record_type'], $e);
        }
    }
}
