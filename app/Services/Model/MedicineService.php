<?php

namespace App\Services\Model;

use App\DTOs\MedicineDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Medicine\InsertMedicineIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineRepository;

class MedicineService 
{
    protected $medicineRepository;
    protected $params;
    public function __construct(MedicineRepository $medicineRepository)
    {
        $this->medicineRepository = $medicineRepository;
    }
    public function withParams(MedicineDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineRepository->applyJoins();
            $data = $this->medicineRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->medicineName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->medicineRepository->applyJoins();
                $data = $this->medicineRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->medicineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicineName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->medicineRepository->applyJoins()
                    ->where('his_medicine.id', $id);
                $data = $this->medicineRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
}