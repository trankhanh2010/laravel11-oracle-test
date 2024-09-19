<?php

namespace App\Services\Model;

use App\DTOs\MedicineGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineGroup\InsertMedicineGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineGroupRepository;

class MedicineGroupService 
{
    protected $medicineGroupRepository;
    protected $params;
    public function __construct(MedicineGroupRepository $medicineGroupRepository)
    {
        $this->medicineGroupRepository = $medicineGroupRepository;
    }
    public function withParams(MedicineGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineGroupRepository->applyJoins();
            $data = $this->medicineGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicineGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->medicineGroupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->medicineGroupRepository->applyJoins();
                $data = $this->medicineGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->medicineGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicineGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicineGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->medicineGroupRepository->applyJoins()
                    ->where('his_medicine_group.id', $id);
                $data = $this->medicineGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_group'], $e);
        }
    }
}
