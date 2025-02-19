<?php

namespace App\Services\Model;

use App\DTOs\TreatmentResultDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentResult\InsertTreatmentResultIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentResultRepository;

class TreatmentResultService 
{
    protected $treatmentResultRepository;
    protected $params;
    public function __construct(TreatmentResultRepository $treatmentResultRepository)
    {
        $this->treatmentResultRepository = $treatmentResultRepository;
    }
    public function withParams(TreatmentResultDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentResultRepository->applyJoins();
            $data = $this->treatmentResultRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentResultRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->treatmentResultRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentResultRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->treatmentResultName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->treatmentResultRepository->applyJoins();
                $data = $this->treatmentResultRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->treatmentResultRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->treatmentResultRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->treatmentResultName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->treatmentResultRepository->applyJoins()
                    ->where('his_treatment_result.id', $id);
                $data = $this->treatmentResultRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }

    public function createTreatmentResult($request)
    {
        try {
            $data = $this->treatmentResultRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentResultIndex($data, $this->params->treatmentResultName));
             // Gọi event để xóa cache
             event(new DeleteCache($this->params->treatmentResultName));           
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }

    public function updateTreatmentResult($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentResultRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentResultRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentResultIndex($data, $this->params->treatmentResultName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentResultName));            
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }

    public function deleteTreatmentResult($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentResultRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentResultRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->treatmentResultName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentResultName));            
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }
}
