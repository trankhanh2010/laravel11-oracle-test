<?php

namespace App\Services\Model;

use App\DTOs\AccidentCareDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AccidentCareRepository;

class AccidentCareService 
{
    protected $accidentCareRepository;
    protected $params;
    public function __construct(AccidentCareRepository $accidentCareRepository)
    {
        $this->accidentCareRepository = $accidentCareRepository;
    }
    public function withParams(AccidentCareDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->accidentCareRepository->applyJoins();
            $data = $this->accidentCareRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->accidentCareRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->accidentCareRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->accidentCareRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->accidentCareName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->accidentCareRepository->applyJoins();
                $data = $this->accidentCareRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->accidentCareRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->accidentCareRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->accidentCareName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->accidentCareRepository->applyJoins()
                    ->where('his_accident_care.id', $id);
                $data = $this->accidentCareRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }

    public function createAccidentCare($request)
    {
        try {
            $data = $this->accidentCareRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentCareName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentCareIndex($data, $this->params->accidentCareName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }

    public function updateAccidentCare($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentCareRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentCareRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentCareName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentCareIndex($data, $this->params->accidentCareName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }

    public function deleteAccidentCare($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentCareRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentCareRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->accidentCareName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->accidentCareName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }
}
