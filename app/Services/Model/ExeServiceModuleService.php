<?php

namespace App\Services\Model;

use App\DTOs\ExeServiceModuleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExeServiceModule\InsertExeServiceModuleIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExeServiceModuleRepository;

class ExeServiceModuleService 
{
    protected $exeServiceModuleRepository;
    protected $params;
    public function __construct(ExeServiceModuleRepository $exeServiceModuleRepository)
    {
        $this->exeServiceModuleRepository = $exeServiceModuleRepository;
    }
    public function withParams(ExeServiceModuleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->exeServiceModuleRepository->applyJoins();
            $data = $this->exeServiceModuleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->exeServiceModuleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->exeServiceModuleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->exeServiceModuleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->exeServiceModuleName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->exeServiceModuleRepository->applyJoins();
                $data = $this->exeServiceModuleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->exeServiceModuleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->exeServiceModuleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->exeServiceModuleName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->exeServiceModuleRepository->applyJoins()
                    ->where('his_exe_service_module.id', $id);
                $data = $this->exeServiceModuleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
}
