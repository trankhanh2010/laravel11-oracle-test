<?php

namespace App\Services\Model;

use App\DTOs\ModuleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Module\InsertModuleIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ModuleRepository;

class ModuleService 
{
    protected $moduleRepository;
    protected $params;
    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }
    public function withParams(ModuleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->moduleRepository->applyJoins();
            $data = $this->moduleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->moduleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->moduleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->moduleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->moduleName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->moduleRepository->applyJoins();
                $data = $this->moduleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->moduleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->moduleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->moduleName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->moduleRepository->applyJoins()
                    ->where('acs_module.id', $id);
                $data = $this->moduleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module'], $e);
        }
    }

    public function createModule($request)
    {
        try {
            $data = $this->moduleRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertModuleIndex($data, $this->params->moduleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->moduleName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module'], $e);
        }
    }

    public function updateModule($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->moduleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->moduleRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertModuleIndex($data, $this->params->moduleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->moduleName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module'], $e);
        }
    }

    public function deleteModule($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->moduleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->moduleRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->moduleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->moduleName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module'], $e);
        }
    }
}
