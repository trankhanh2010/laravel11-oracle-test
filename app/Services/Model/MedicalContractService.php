<?php

namespace App\Services\Model;

use App\DTOs\MedicalContractDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicalContract\InsertMedicalContractIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicalContractRepository;

class MedicalContractService 
{
    protected $medicalContractRepository;
    protected $params;
    public function __construct(MedicalContractRepository $medicalContractRepository)
    {
        $this->medicalContractRepository = $medicalContractRepository;
    }
    public function withParams(MedicalContractDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicalContractRepository->applyJoins();
            $data = $this->medicalContractRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicalContractRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicalContractRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicalContractRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->medicalContractName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->medicalContractRepository->applyJoins();
                $data = $this->medicalContractRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->medicalContractRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicalContractRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicalContractName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->medicalContractRepository->applyJoins()
                    ->where('his_medical_contract.id', $id);
                $data = $this->medicalContractRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }

    public function createMedicalContract($request)
    {
        try {
            $data = $this->medicalContractRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMedicalContractIndex($data, $this->params->medicalContractName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicalContractName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }

    public function updateMedicalContract($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicalContractRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicalContractRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMedicalContractIndex($data, $this->params->medicalContractName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicalContractName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }

    public function deleteMedicalContract($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicalContractRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicalContractRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicalContractName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicalContractName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }
}
