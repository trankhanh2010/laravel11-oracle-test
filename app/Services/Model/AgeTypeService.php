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
    protected $age_type_repository;
    protected $request;
    public function __construct(Request $request, AgeTypeRepository $age_type_repository)
    {
        parent::__construct($request);
        $this->request = $request;
        $this->age_type_repository = $age_type_repository;
    }

    public function handleDataBaseSearch($keyword, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        try {
            $data = $this->age_type_repository->applyJoins();
            $data = $this->age_type_repository->applyKeywordFilter($data, $keyword);
            $data = $this->age_type_repository->applyIsActiveFilter($data, $is_active);
            $count = $data->count();
            $data = $this->age_type_repository->applyOrdering($data, $order_by, $order_by_join);
            $data = $this->age_type_repository->fetchData($data, $get_all, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['age_type'], config('params')['db_service']['error']['age_type'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
    public function handleDataBaseGetAll($age_type_name, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        try {
            $data = Cache::remember($age_type_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () use ($is_active, $order_by, $order_by_join, $get_all, $start, $limit) {
                $data = $this->age_type_repository->applyJoins();
                $data = $this->age_type_repository->applyIsActiveFilter($data, $is_active);
                $count = $data->count();
                $data = $this->age_type_repository->applyOrdering($data, $order_by, $order_by_join);
                $data = $this->age_type_repository->fetchData($data, $get_all, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['age_type'], config('params')['db_service']['error']['age_type'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
    public function handleDataBaseGetWithId($age_type_name, $id, $is_active)
    {
        try {
            $data = Cache::remember($age_type_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id, $is_active) {
                $data = $this->age_type_repository->applyJoins()
                    ->where('his_age_type.id', $id);
                $data = $this->age_type_repository->applyIsActiveFilter($data, $is_active);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['age_type'], config('params')['db_service']['error']['age_type'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
}
