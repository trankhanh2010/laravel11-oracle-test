<?php

namespace App\Services\Model;

use App\DTOs\SuimIndexDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SuimIndex\InsertSuimIndexIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SuimIndexRepository;

class SuimIndexService 
{
    protected $suimIndexRepository;
    protected $params;
    public function __construct(SuimIndexRepository $suimIndexRepository)
    {
        $this->suimIndexRepository = $suimIndexRepository;
    }
    public function withParams(SuimIndexDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->suimIndexRepository->applyJoins();
            $data = $this->suimIndexRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->suimIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->suimIndexRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->suimIndexRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->suimIndexName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->suimIndexRepository->applyJoins();
                $data = $this->suimIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->suimIndexRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->suimIndexRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->suimIndexName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->suimIndexRepository->applyJoins()
                    ->where('his_suim_index.id', $id);
                $data = $this->suimIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
}
