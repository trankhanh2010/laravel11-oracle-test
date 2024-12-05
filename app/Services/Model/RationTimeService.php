<?php

namespace App\Services\Model;

use App\DTOs\RationTimeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\RationTime\InsertRationTimeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RationTimeRepository;

class RationTimeService 
{
    protected $rationTimeRepository;
    protected $params;
    public function __construct(RationTimeRepository $rationTimeRepository)
    {
        $this->rationTimeRepository = $rationTimeRepository;
    }
    public function withParams(RationTimeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->rationTimeRepository->applyJoins();
            $data = $this->rationTimeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->rationTimeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->rationTimeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->rationTimeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_time'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->rationTimeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->rationTimeRepository->applyJoins();
                $data = $this->rationTimeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->rationTimeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->rationTimeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_time'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->rationTimeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->rationTimeRepository->applyJoins()
                    ->where('his_ration_time.id', $id);
                $data = $this->rationTimeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_time'], $e);
        }
    }

    public function createRationTime($request)
    {
        try {
            $data = $this->rationTimeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertRationTimeIndex($data, $this->params->rationTimeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationTimeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_time'], $e);
        }
    }

    public function updateRationTime($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->rationTimeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->rationTimeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertRationTimeIndex($data, $this->params->rationTimeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationTimeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_time'], $e);
        }
    }

    public function deleteRationTime($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->rationTimeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->rationTimeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->rationTimeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationTimeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_time'], $e);
        }
    }
}
