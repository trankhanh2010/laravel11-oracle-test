<?php

namespace App\Services\Model;

use App\DTOs\HospitalizeReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\HospitalizeReason\InsertHospitalizeReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\HospitalizeReasonRepository;

class HospitalizeReasonService 
{
    protected $hospitalizeReasonRepository;
    protected $params;
    public function __construct(HospitalizeReasonRepository $hospitalizeReasonRepository)
    {
        $this->hospitalizeReasonRepository = $hospitalizeReasonRepository;
    }
    public function withParams(HospitalizeReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->hospitalizeReasonRepository->applyJoins();
            $data = $this->hospitalizeReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->hospitalizeReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->hospitalizeReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->hospitalizeReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hospitalize_reason'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->hospitalizeReasonName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->hospitalizeReasonRepository->applyJoins();
                $data = $this->hospitalizeReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->hospitalizeReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->hospitalizeReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hospitalize_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->hospitalizeReasonName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->hospitalizeReasonRepository->applyJoins()
                    ->where('his_hospitalize_reason.id', $id);
                $data = $this->hospitalizeReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hospitalize_reason'], $e);
        }
    }

    public function createHospitalizeReason($request)
    {
        try {
            $data = $this->hospitalizeReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertHospitalizeReasonIndex($data, $this->params->hospitalizeReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->hospitalizeReasonName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hospitalize_reason'], $e);
        }
    }

    public function updateHospitalizeReason($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->hospitalizeReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->hospitalizeReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertHospitalizeReasonIndex($data, $this->params->hospitalizeReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->hospitalizeReasonName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hospitalize_reason'], $e);
        }
    }

    public function deleteHospitalizeReason($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->hospitalizeReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->hospitalizeReasonRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->hospitalizeReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->hospitalizeReasonName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hospitalize_reason'], $e);
        }
    }
}
