<?php

namespace App\Services\Model;

use App\DTOs\DosageFormDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DosageForm\InsertDosageFormIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DosageFormRepository;
use Illuminate\Support\Facades\Redis;

class DosageFormService 
{
    protected $dosageFormRepository;
    protected $params;
    public function __construct(DosageFormRepository $dosageFormRepository)
    {
        $this->dosageFormRepository = $dosageFormRepository;
    }
    public function withParams(DosageFormDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->dosageFormRepository->applyJoins();
            $data = $this->dosageFormRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->dosageFormRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->dosageFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->dosageFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dosage_form'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->dosageFormName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->dosageFormName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->dosageFormRepository->applyJoins();
                $data = $this->dosageFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->dosageFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->dosageFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dosage_form'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->dosageFormName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->dosageFormRepository->applyJoins()
                    ->where('his_dosage_form.id', $id);
                $data = $this->dosageFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dosage_form'], $e);
        }
    }

    public function createDosageForm($request)
    {
        try {
            $data = $this->dosageFormRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDosageFormIndex($data, $this->params->dosageFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->dosageFormName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dosage_form'], $e);
        }
    }

    public function updateDosageForm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->dosageFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->dosageFormRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDosageFormIndex($data, $this->params->dosageFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->dosageFormName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dosage_form'], $e);
        }
    }

    public function deleteDosageForm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->dosageFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->dosageFormRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->dosageFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->dosageFormName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dosage_form'], $e);
        }
    }
}
