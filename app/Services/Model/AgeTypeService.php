<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AgeType\InsertAgeTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AgeTypeRepository;

class AgeTypeService extends BaseApiCacheController
{
    protected $ageTypeRepository;
    protected $request;
    public function __construct(Request $request, AgeTypeRepository $ageTypeRepository)
    {
        parent::__construct($request);
        $this->ageTypeRepository = $ageTypeRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->ageTypeRepository->applyJoins();
            $data = $this->ageTypeRepository->applyKeywordFilter($data, $keyword);
            $data = $this->ageTypeRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->ageTypeRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->ageTypeRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }
    public function handleDataBaseGetAll($ageTypeName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($ageTypeName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->ageTypeRepository->applyJoins();
                $data = $this->ageTypeRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->ageTypeRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->ageTypeRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($ageTypeName, $id, $isActive)
    {
        try {
            $data = Cache::remember($ageTypeName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->ageTypeRepository->applyJoins()
                    ->where('his_age_type.id', $id);
                $data = $this->ageTypeRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }
}
