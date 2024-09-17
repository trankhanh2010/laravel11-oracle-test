<?php

namespace App\Services\Model;

use App\DTOs\HeinServiceTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\HeinServiceType\InsertHeinServiceTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\HeinServiceTypeRepository;

class HeinServiceTypeService 
{
    protected $heinServiceTypeRepository;
    protected $params;
    public function __construct(HeinServiceTypeRepository $heinServiceTypeRepository)
    {
        $this->heinServiceTypeRepository = $heinServiceTypeRepository;
    }
    public function withParams(HeinServiceTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->heinServiceTypeRepository->applyJoins();
            $data = $this->heinServiceTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->heinServiceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->heinServiceTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->heinServiceTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->heinServiceTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->heinServiceTypeRepository->applyJoins();
                $data = $this->heinServiceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->heinServiceTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->heinServiceTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->heinServiceTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->heinServiceTypeRepository->applyJoins()
                    ->where('his_hein_service_type.id', $id);
                $data = $this->heinServiceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
    public function deleteHeinServiceType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->heinServiceTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->heinServiceTypeRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->heinServiceTypeName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->heinServiceTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
}
