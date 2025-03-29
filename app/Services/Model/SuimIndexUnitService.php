<?php

namespace App\Services\Model;

use App\DTOs\SuimIndexUnitDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SuimIndexUnit\InsertSuimIndexUnitIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SuimIndexUnitRepository;
use Illuminate\Support\Facades\Redis;

class SuimIndexUnitService 
{
    protected $suimIndexUnitRepository;
    protected $params;
    public function __construct(SuimIndexUnitRepository $suimIndexUnitRepository)
    {
        $this->suimIndexUnitRepository = $suimIndexUnitRepository;
    }
    public function withParams(SuimIndexUnitDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->suimIndexUnitRepository->applyJoins();
            $data = $this->suimIndexUnitRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->suimIndexUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->suimIndexUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->suimIndexUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index_unit'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->suimIndexUnitName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->suimIndexUnitName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->suimIndexUnitRepository->applyJoins();
                $data = $this->suimIndexUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->suimIndexUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->suimIndexUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index_unit'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->suimIndexUnitName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->suimIndexUnitName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->suimIndexUnitRepository->applyJoins()
                    ->where('his_suim_index_unit.id', $id);
                $data = $this->suimIndexUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index_unit'], $e);
        }
    }

    public function createSuimIndexUnit($request)
    {
        try {
            $data = $this->suimIndexUnitRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSuimIndexUnitIndex($data, $this->params->suimIndexUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->suimIndexUnitName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index_unit'], $e);
        }
    }

    public function updateSuimIndexUnit($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->suimIndexUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->suimIndexUnitRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSuimIndexUnitIndex($data, $this->params->suimIndexUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->suimIndexUnitName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index_unit'], $e);
        }
    }

    public function deleteSuimIndexUnit($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->suimIndexUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->suimIndexUnitRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->suimIndexUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->suimIndexUnitName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index_unit'], $e);
        }
    }
}
