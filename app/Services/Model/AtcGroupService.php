<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AtcGroup\InsertAtcGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AtcGroupRepository;

class AtcGroupService extends BaseApiCacheController
{
    protected $atcGroupRepository;
    protected $request;
    public function __construct(Request $request, AtcGroupRepository $atcGroupRepository)
    {
        parent::__construct($request);
        $this->atcGroupRepository = $atcGroupRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->atcGroupRepository->applyJoins();
            $data = $this->atcGroupRepository->applyKeywordFilter($data, $keyword);
            $data = $this->atcGroupRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->atcGroupRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->atcGroupRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }
    public function handleDataBaseGetAll($atcGroupName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($atcGroupName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->atcGroupRepository->applyJoins();
                $data = $this->atcGroupRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->atcGroupRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->atcGroupRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($atcGroupName, $id, $isActive)
    {
        try {
            $data = Cache::remember($atcGroupName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->atcGroupRepository->applyJoins()
                    ->where('his_atc_group.id', $id);
                $data = $this->atcGroupRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }

    public function createAtcGroup($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->atcGroupRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->atcGroupName));
            // Gọi event để thêm index vào elastic
            event(new InsertAtcGroupIndex($data, $this->atcGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }

    public function updateAtcGroup($atcGroupName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->atcGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->atcGroupRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($atcGroupName));
            // Gọi event để thêm index vào elastic
            event(new InsertAtcGroupIndex($data, $atcGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }

    public function deleteAtcGroup($atcGroupName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->atcGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->atcGroupRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($atcGroupName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $atcGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['atc_group'], $e);
        }
    }
}
