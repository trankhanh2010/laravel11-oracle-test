<?php

namespace App\Services\Model;

use App\DTOs\OtherPaySourceDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\OtherPaySource\InsertOtherPaySourceIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\OtherPaySourceRepository;

class OtherPaySourceService 
{
    protected $otherPaySourceRepository;
    protected $params;
    public function __construct(OtherPaySourceRepository $otherPaySourceRepository)
    {
        $this->otherPaySourceRepository = $otherPaySourceRepository;
    }
    public function withParams(OtherPaySourceDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->otherPaySourceRepository->applyJoins();
            $data = $this->otherPaySourceRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->otherPaySourceRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->otherPaySourceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->otherPaySourceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->otherPaySourceName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->otherPaySourceRepository->applyJoins();
                $data = $this->otherPaySourceRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->otherPaySourceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->otherPaySourceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->otherPaySourceName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->otherPaySourceRepository->applyJoins()
                    ->where('his_other_pay_source.id', $id);
                $data = $this->otherPaySourceRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }

    public function createOtherPaySource($request)
    {
        try {
            $data = $this->otherPaySourceRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertOtherPaySourceIndex($data, $this->params->otherPaySourceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->otherPaySourceName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }

    public function updateOtherPaySource($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->otherPaySourceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->otherPaySourceRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertOtherPaySourceIndex($data, $this->params->otherPaySourceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->otherPaySourceName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }

    public function deleteOtherPaySource($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->otherPaySourceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->otherPaySourceRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->otherPaySourceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->otherPaySourceName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }
}
