<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AccidentCareRepository;

class AccidentCareService extends BaseApiCacheController
{
    protected $accidentCareRepository;
    protected $request;
    public function __construct(Request $request, AccidentCareRepository $accidentCareRepository)
    {
        parent::__construct($request);
        $this->accidentCareRepository = $accidentCareRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->accidentCareRepository->applyJoins();
            $data = $this->accidentCareRepository->applyKeywordFilter($data, $keyword);
            $data = $this->accidentCareRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->accidentCareRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->accidentCareRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }
    public function handleDataBaseGetAll($accidentCareName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($accidentCareName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->accidentCareRepository->applyJoins();
                $data = $this->accidentCareRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->accidentCareRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->accidentCareRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }
    public function handleDataBaseGetWithId($accidentCareName, $id, $isActive)
    {
        try {
            $data = Cache::remember($accidentCareName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->accidentCareRepository->applyJoins()
                    ->where('his_accident_care.id', $id);
                $data = $this->accidentCareRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }

    public function createAccidentCare($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->accidentCareRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->accidentCareName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentCareIndex($data, $this->accidentCareName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }

    public function updateAccidentCare($accidentCareName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentCareRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentCareRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentCareName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentCareIndex($data, $accidentCareName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }

    public function deleteAccidentCare($accidentCareName, $id)
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
            event(new DeleteCache($accidentCareName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $accidentCareName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_care'], $e);
        }
    }
}
