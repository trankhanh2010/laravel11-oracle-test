<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentLocation\InsertAccidentLocationIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AccidentLocationRepository;

class AccidentLocationService extends BaseApiCacheController
{
    protected $accidentLocationRepository;
    protected $request;
    public function __construct(Request $request, AccidentLocationRepository $accidentLocationRepository)
    {
        parent::__construct($request);
        $this->accidentLocationRepository = $accidentLocationRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->accidentLocationRepository->applyJoins();
            $data = $this->accidentLocationRepository->applyKeywordFilter($data, $keyword);
            $data = $this->accidentLocationRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->accidentLocationRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->accidentLocationRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }
    public function handleDataBaseGetAll($accidentLocationName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($accidentLocationName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->accidentLocationRepository->applyJoins();
                $data = $this->accidentLocationRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->accidentLocationRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->accidentLocationRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }
    public function handleDataBaseGetWithId($accidentLocationName, $id, $isActive)
    {
        try {
            $data = Cache::remember($accidentLocationName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->accidentLocationRepository->applyJoins()
                    ->where('his_accident_location.id', $id);
                $data = $this->accidentLocationRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }

    public function createAccidentLocation($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->accidentLocationRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->accidentLocationName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentLocationIndex($data, $this->accidentLocationName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }

    public function updateAccidentLocation($accidentLocationName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentLocationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentLocationRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentLocationName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentLocationIndex($data, $accidentLocationName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }

    public function deleteAccidentLocation($accidentLocationName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentLocationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentLocationRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentLocationName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $accidentLocationName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_location'], $e);
        }
    }
}
