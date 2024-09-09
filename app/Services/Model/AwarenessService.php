<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Awareness\InsertAwarenessIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AwarenessRepository;

class AwarenessService extends BaseApiCacheController
{
    protected $awarenessRepository;
    protected $request;
    public function __construct(Request $request, AwarenessRepository $awarenessRepository)
    {
        parent::__construct($request);
        $this->awarenessRepository = $awarenessRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->awarenessRepository->applyJoins();
            $data = $this->awarenessRepository->applyKeywordFilter($data, $keyword);
            $data = $this->awarenessRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->awarenessRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->awarenessRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
    public function handleDataBaseGetAll($awarenessName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($awarenessName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->awarenessRepository->applyJoins();
                $data = $this->awarenessRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->awarenessRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->awarenessRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
    public function handleDataBaseGetWithId($awarenessName, $id, $isActive)
    {
        try {
            $data = Cache::remember($awarenessName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->awarenessRepository->applyJoins()
                    ->where('his_awareness.id', $id);
                $data = $this->awarenessRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function createAwareness($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->awarenessRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->awarenessName));
            // Gọi event để thêm index vào elastic
            event(new InsertAwarenessIndex($data, $this->awarenessName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function updateAwareness($awarenessName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->awarenessRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->awarenessRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($awarenessName));
            // Gọi event để thêm index vào elastic
            event(new InsertAwarenessIndex($data, $awarenessName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function deleteAwareness($awarenessName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->awarenessRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->awarenessRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($awarenessName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $awarenessName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
}
