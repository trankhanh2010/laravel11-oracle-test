<?php

namespace App\Services\Model;

use App\DTOs\MedicinePatyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicinePaty\InsertMedicinePatyIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicinePatyRepository;

class MedicinePatyService 
{
    protected $medicinePatyRepository;
    protected $params;
    public function __construct(MedicinePatyRepository $medicinePatyRepository)
    {
        $this->medicinePatyRepository = $medicinePatyRepository;
    }
    public function withParams(MedicinePatyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicinePatyRepository->applyJoins();
            $data = $this->medicinePatyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicinePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicinePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicinePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->medicinePatyName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->medicinePatyRepository->applyJoins();
                $data = $this->medicinePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->medicinePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicinePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicinePatyName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->medicinePatyRepository->applyJoins()
                    ->where('his_medicine_paty.id', $id);
                $data = $this->medicinePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }

    public function createMedicinePaty($request)
    {
        try {
            $data = $this->medicinePatyRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMedicinePatyIndex($data, $this->params->medicinePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicinePatyName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }

    public function updateMedicinePaty($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicinePatyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicinePatyRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMedicinePatyIndex($data, $this->params->medicinePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicinePatyName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }

    public function deleteMedicinePaty($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicinePatyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicinePatyRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicinePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicinePatyName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }
}
