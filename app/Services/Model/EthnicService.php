<?php

namespace App\Services\Model;

use App\DTOs\EthnicDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Ethnic\InsertEthnicIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EthnicRepository;

class EthnicService 
{
    protected $ethnicRepository;
    protected $params;
    public function __construct(EthnicRepository $ethnicRepository)
    {
        $this->ethnicRepository = $ethnicRepository;
    }
    public function withParams(EthnicDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->ethnicRepository->applyJoins();
            $data = $this->ethnicRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ethnicRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ethnicRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ethnicRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ethnic'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->ethnicName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->ethnicRepository->applyJoins();
                $data = $this->ethnicRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->ethnicRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->ethnicRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ethnic'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->ethnicName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->ethnicRepository->applyJoins()
                    ->where('sda_ethnic.id', $id);
                $data = $this->ethnicRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ethnic'], $e);
        }
    }

    public function createEthnic($request)
    {
        try {
            $data = $this->ethnicRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertEthnicIndex($data, $this->params->ethnicName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ethnicName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ethnic'], $e);
        }
    }

    public function updateEthnic($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ethnicRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ethnicRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertEthnicIndex($data, $this->params->ethnicName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ethnicName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ethnic'], $e);
        }
    }

    public function deleteEthnic($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ethnicRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ethnicRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ethnicName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ethnicName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ethnic'], $e);
        }
    }
}
