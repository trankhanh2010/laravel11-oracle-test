<?php

namespace App\Services\Model;

use App\DTOs\ServSegrDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServSegr\InsertServSegrIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServSegrRepository;

class ServSegrService 
{
    protected $servSegrRepository;
    protected $params;
    public function __construct(ServSegrRepository $servSegrRepository)
    {
        $this->servSegrRepository = $servSegrRepository;
    }
    public function withParams(ServSegrDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->servSegrRepository->applyJoins();
            $data = $this->servSegrRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->servSegrRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->servSegrRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->servSegrRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['serv_segr'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->servSegrName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->servSegrRepository->applyJoins();
                $data = $this->servSegrRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->servSegrRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->servSegrRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['serv_segr'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->servSegrName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->servSegrRepository->applyJoins()
                    ->where('his_serv_segr.id', $id);
                $data = $this->servSegrRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['serv_segr'], $e);
        }
    }
}
