<?php

namespace App\Services\Model;

use App\DTOs\AtcDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Atc\InsertAtcIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AtcRepository;

class AtcService 
{
    protected $atcRepository;
    protected $params;
    public function __construct(AtcRepository $atcRepository)
    {
        $this->atcRepository = $atcRepository;
    }
    public function withParams(AtcDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->atcRepository->applyJoins();
            $data = $this->atcRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->atcRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->atcRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->atcRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->atcName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->atcRepository->applyJoins();
                $data = $this->atcRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->atcRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->atcRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->atcName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->atcRepository->applyJoins()
                    ->where('his_atc.id', $id);
                $data = $this->atcRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc'], $e);
        }
    }

    public function createAtc($request)
    {
        try {
            $data = $this->atcRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertAtcIndex($data, $this->params->atcName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->atcName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc'], $e);
        }
    }

    public function updateAtc($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->atcRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->atcRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertAtcIndex($data, $this->params->atcName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->atcName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc'], $e);
        }
    }

    public function deleteAtc($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->atcRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->atcRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->atcName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->atcName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc'], $e);
        }
    }
}
