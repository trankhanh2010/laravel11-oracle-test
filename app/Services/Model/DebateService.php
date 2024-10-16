<?php

namespace App\Services\Model;

use App\DTOs\DebateDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReq\InsertServiceReqIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateRepository;

class DebateService
{
    protected $debateRepository;
    protected $params;
    public function __construct(DebateRepository $debateRepository)
    {
        $this->debateRepository = $debateRepository;
    }
    public function withParams(DebateDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateRepository->applyJoins();
            $data = $this->debateRepository->applyWith($data);
            $data = $this->debateRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->debateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->debateRepository->applyJoins();
            $data = $this->debateRepository->applyWith($data);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->debateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->debateRepository->applyJoins()
                ->where('his_debate.id', $id);
            $data = $this->debateRepository->applyWith($data);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
}
