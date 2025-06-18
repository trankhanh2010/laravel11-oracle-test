<?php

namespace App\Services\Model;

use App\DTOs\TranPatiTempDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TranPatiTemp\InsertTranPatiTempIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TranPatiTempRepository;
use Illuminate\Support\Facades\Redis;

class TranPatiTempService
{
    protected $tranPatiTempRepository;
    protected $params;
    public function __construct(TranPatiTempRepository $tranPatiTempRepository)
    {
        $this->tranPatiTempRepository = $tranPatiTempRepository;
    }
    public function withParams(TranPatiTempDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->tranPatiTempRepository->applyJoins();
            $data = $this->tranPatiTempRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->tranPatiTempRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->tranPatiTempRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->tranPatiTempRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_temp'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->tranPatiTempRepository->applyJoins();
        $data = $this->tranPatiTempRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->tranPatiTempRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->tranPatiTempRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseSelectByLoginname()
    {
        $data = $this->tranPatiTempRepository->applyJoins();
        $data = $this->tranPatiTempRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->tranPatiTempRepository->applySelectByLoginnameFilter($data, $this->params->currentLoginname);
        $count = $data->count();
        $data = $this->tranPatiTempRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->tranPatiTempRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->tranPatiTempRepository->applyJoins()
            ->where('his_tran_pati_temp.id', $id);
        $data = $this->tranPatiTempRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAllDataFromDatabaseSelectByLoginname()
    {
        try {
            return $this->getAllDataFromDatabaseSelectByLoginname();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_temp'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {

            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_temp'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_temp'], $e);
        }
    }
}
