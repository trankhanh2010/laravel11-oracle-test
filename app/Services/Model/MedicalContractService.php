<?php

namespace App\Services\Model;

use App\DTOs\MedicalContractDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicalContract\InsertMedicalContractIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicalContractRepository;
use Illuminate\Support\Facades\Redis;

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
    private function getAllDataFromDatabase()
    {
        $data = $this->medicalContractRepository->applyJoins();
        $data = $this->medicalContractRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->medicalContractRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->medicalContractRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicalContractRepository->applyJoins()
            ->where('his_medical_contract.id', $id);
        $data = $this->medicalContractRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->medicalContractName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicalContractName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_contract'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicalContractName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicalContractName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
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
