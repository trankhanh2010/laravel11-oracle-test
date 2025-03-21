<?php

namespace App\Services\Model;

use App\DTOs\IcdListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\IcdListVView\InsertIcdListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\IcdListVViewRepository;

class IcdListVViewService 
{
    protected $icdListVViewRepository;
    protected $params;
    public function __construct(IcdListVViewRepository $icdListVViewRepository)
    {
        $this->icdListVViewRepository = $icdListVViewRepository;
    }
    public function withParams(IcdListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->icdListVViewRepository->applyJoins();
            $data = $this->icdListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->icdListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->icdListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->icdListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->icdListVViewName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->icdListVViewRepository->applyJoins();
                $data = $this->icdListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->icdListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->icdListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->icdListVViewName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->icdListVViewRepository->applyJoins()
                    ->where('id', $id);
                $data = $this->icdListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_list_v_view'], $e);
        }
    }

}
