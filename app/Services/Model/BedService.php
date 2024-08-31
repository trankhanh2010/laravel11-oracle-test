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
    protected $bed_repository;
    protected $request;
    public function __construct(Request $request, BedRepository $bed_repository)
    {
        parent::__construct($request);
        $this->bed_repository = $bed_repository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        try {
            $data = $this->bed_repository->applyJoins();
            $data = $this->bed_repository->applyKeywordFilter($data, $keyword);
            $data = $this->bed_repository->applyIsActiveFilter($data, $is_active);
            $count = $data->count();
            $data = $this->bed_repository->applyOrdering($data, $order_by, $order_by_join);
            $data = $this->bed_repository->fetchData($data, $get_all, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
    public function handleDataBaseGetAll($bed_name, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        try {
            $data = Cache::remember($bed_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () use ($is_active, $order_by, $order_by_join, $get_all, $start, $limit) {
                $data = $this->bed_repository->applyJoins();
                $data = $this->bed_repository->applyIsActiveFilter($data, $is_active);
                $count = $data->count();
                $data = $this->bed_repository->applyOrdering($data, $order_by, $order_by_join);
                $data = $this->bed_repository->fetchData($data, $get_all, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
    public function handleDataBaseGetWithId($bed_name, $id, $is_active)
    {
        try {
            $data = Cache::remember($bed_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id, $is_active) {
                $data = $this->bed_repository->applyJoins()
                    ->where('his_bed.id', $id);
                $data = $this->bed_repository->applyIsActiveFilter($data, $is_active);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }

    public function createBed($request, $time, $app_creator, $app_modifier)
    {
        try {
            $data = $this->bed_repository->create($request, $time, $app_creator, $app_modifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->bed_name));
            // Gọi event để thêm index vào elastic
            event(new InsertBedIndex($data, $this->bed_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }

    public function updateBed($bed_name, $id, $request, $time, $app_modifier)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->bed_repository->getById($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data = $this->bed_repository->update($request, $data, $time, $app_modifier);
            // Gọi event để xóa cache
            event(new DeleteCache($bed_name));
            // Gọi event để thêm index vào elastic
            event(new InsertBedIndex($data, $bed_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }

    public function deleteBed($bed_name, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->bed_repository->getById($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data = $this->bed_repository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($bed_name));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $bed_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            return write_and_throw_error(config('params')['db_service']['error']['bed'], config('params')['db_service']['error']['bed'], $e, __FUNCTION__, __CLASS__, $this->request);
        }
    }
}
