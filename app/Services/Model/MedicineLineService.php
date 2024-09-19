<?php

namespace App\Services\Model;

use App\DTOs\MedicineLineDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineLine\InsertMedicineLineIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineLineRepository;

class MedicineLineService 
{
    protected $medicineLineRepository;
    protected $params;
    public function __construct(MedicineLineRepository $medicineLineRepository)
    {
        $this->medicineLineRepository = $medicineLineRepository;
    }
    public function withParams(MedicineLineDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineLineRepository->applyJoins();
            $data = $this->medicineLineRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineLineRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicineLineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineLineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->medicineLineName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->medicineLineRepository->applyJoins();
                $data = $this->medicineLineRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->medicineLineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicineLineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicineLineName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->medicineLineRepository->applyJoins()
                    ->where('his_medicine_line.id', $id);
                $data = $this->medicineLineRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
}
