<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\BedRepository;

class BedService extends BaseApiCacheController
{
    protected $bedRepository;
    protected $request;
    public function __construct(Request $request, BedRepository $bedRepository)
    {
        parent::__construct($request);
        $this->bedRepository = $bedRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->bedRepository->applyJoins();
            $data = $this->bedRepository->applyKeywordFilter($data, $keyword);
            $data = $this->bedRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->bedRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->bedRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
    public function handleDataBaseGetAll($bedName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($bedName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->bedRepository->applyJoins();
                $data = $this->bedRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->bedRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->bedRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
    public function handleDataBaseGetWithId($bedName, $id, $isActive)
    {
        try {
            $data = Cache::remember($bedName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->bedRepository->applyJoins()
                    ->where('his_bed.id', $id);
                $data = $this->bedRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }

    public function createBed($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->bedRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->bedName));
            // Gọi event để thêm index vào elastic
            event(new InsertBedIndex($data, $this->bedName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }

    public function updateBed($bedName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($bedName));
            // Gọi event để thêm index vào elastic
            event(new InsertBedIndex($data, $bedName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }

    public function deleteBed($bedName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($bedName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $bedName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
}
