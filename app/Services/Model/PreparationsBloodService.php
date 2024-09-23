<?php

namespace App\Services\Model;

use App\DTOs\PreparationsBloodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PreparationsBlood\InsertPreparationsBloodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PreparationsBloodRepository;

class PreparationsBloodService 
{
    protected $preparationsBloodRepository;
    protected $params;
    public function __construct(PreparationsBloodRepository $preparationsBloodRepository)
    {
        $this->preparationsBloodRepository = $preparationsBloodRepository;
    }
    public function withParams(PreparationsBloodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->preparationsBloodRepository->applyJoins();
            $data = $this->preparationsBloodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->preparationsBloodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->preparationsBloodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->preparationsBloodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->preparationsBloodName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->preparationsBloodRepository->applyJoins();
                $data = $this->preparationsBloodRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->preparationsBloodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->preparationsBloodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->preparationsBloodName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->preparationsBloodRepository->applyJoins()
                    ->where('his_preparations_blood.id', $id);
                $data = $this->preparationsBloodRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }

    public function createPreparationsBlood($request)
    {
        try {
            $data = $this->preparationsBloodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->preparationsBloodName));
            // Gọi event để thêm index vào elastic
            event(new InsertPreparationsBloodIndex($data, $this->params->preparationsBloodName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }

    public function updatePreparationsBlood($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->preparationsBloodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->preparationsBloodRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->preparationsBloodName));
            // Gọi event để thêm index vào elastic
            event(new InsertPreparationsBloodIndex($data, $this->params->preparationsBloodName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }

    public function deletePreparationsBlood($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->preparationsBloodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->preparationsBloodRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->preparationsBloodName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->preparationsBloodName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }
}
