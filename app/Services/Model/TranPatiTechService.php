<?php

namespace App\Services\Model;

use App\DTOs\TranPatiTechDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TranPatiTech\InsertTranPatiTechIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TranPatiTechRepository;

class TranPatiTechService 
{
    protected $tranPatiTechRepository;
    protected $params;
    public function __construct(TranPatiTechRepository $tranPatiTechRepository)
    {
        $this->tranPatiTechRepository = $tranPatiTechRepository;
    }
    public function withParams(TranPatiTechDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->tranPatiTechRepository->applyJoins();
            $data = $this->tranPatiTechRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->tranPatiTechRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->tranPatiTechRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->tranPatiTechRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_tech'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->tranPatiTechName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->tranPatiTechRepository->applyJoins();
                $data = $this->tranPatiTechRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->tranPatiTechRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->tranPatiTechRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_tech'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->tranPatiTechName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->tranPatiTechRepository->applyJoins()
                    ->where('his_tran_pati_tech.id', $id);
                $data = $this->tranPatiTechRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_tech'], $e);
        }
    }

    public function createTranPatiTech($request)
    {
        try {
            $data = $this->tranPatiTechRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTranPatiTechIndex($data, $this->params->tranPatiTechName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->tranPatiTechName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_tech'], $e);
        }
    }

    public function updateTranPatiTech($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->tranPatiTechRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->tranPatiTechRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTranPatiTechIndex($data, $this->params->tranPatiTechName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->tranPatiTechName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_tech'], $e);
        }
    }

    public function deleteTranPatiTech($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->tranPatiTechRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->tranPatiTechRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->tranPatiTechName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->tranPatiTechName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_tech'], $e);
        }
    }
}
