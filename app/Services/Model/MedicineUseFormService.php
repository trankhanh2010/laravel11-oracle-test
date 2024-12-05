<?php

namespace App\Services\Model;

use App\DTOs\MedicineUseFormDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineUseForm\InsertMedicineUseFormIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineUseFormRepository;

class MedicineUseFormService 
{
    protected $medicineUseFormRepository;
    protected $params;
    public function __construct(MedicineUseFormRepository $medicineUseFormRepository)
    {
        $this->medicineUseFormRepository = $medicineUseFormRepository;
    }
    public function withParams(MedicineUseFormDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineUseFormRepository->applyJoins();
            $data = $this->medicineUseFormRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineUseFormRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicineUseFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineUseFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->medicineUseFormName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->medicineUseFormRepository->applyJoins();
                $data = $this->medicineUseFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->medicineUseFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicineUseFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicineUseFormName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->medicineUseFormRepository->applyJoins()
                    ->where('his_medicine_use_form.id', $id);
                $data = $this->medicineUseFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }

    public function createMedicineUseForm($request)
    {
        try {
            $data = $this->medicineUseFormRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMedicineUseFormIndex($data, $this->params->medicineUseFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineUseFormName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }

    public function updateMedicineUseForm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineUseFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineUseFormRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMedicineUseFormIndex($data, $this->params->medicineUseFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineUseFormName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }

    public function deleteMedicineUseForm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineUseFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineUseFormRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicineUseFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineUseFormName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }
}
