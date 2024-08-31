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
    protected $accident_location_repository;
    public function __construct(Request $request, AccidentLocationRepository $accident_location_repository)
    {
        parent::__construct($request);
        $this->accident_location_repository = $accident_location_repository;
    }

    public function handleDataBaseSearch($keyword, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        $data = $this->accident_location_repository->applyJoins();
        $data = $this->accident_location_repository->applyKeywordFilter($data, $keyword);
        $data = $this->accident_location_repository->applyIsActiveFilter($data, $is_active);
        $count = $data->count();
        $data = $this->accident_location_repository->applyOrdering($data, $order_by, $order_by_join);
        $data = $this->accident_location_repository->fetchData($data, $get_all, $start, $limit);
        return ['data' => $data, 'count' => $count];
    }
    public function handleDataBaseGetAll($accident_location_name, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        $data = Cache::remember($accident_location_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () use ($is_active, $order_by, $order_by_join, $get_all, $start, $limit) {
            $data = $this->accident_location_repository->applyJoins();
            $data = $this->accident_location_repository->applyIsActiveFilter($data, $is_active);
            $count = $data->count();
            $data = $this->accident_location_repository->applyOrdering($data, $order_by, $order_by_join);
            $data = $this->accident_location_repository->fetchData($data, $get_all, $start, $limit);
            return ['data' => $data, 'count' => $count];
        });
        return $data;
    }
    public function handleDataBaseGetWithId($accident_location_name, $id, $is_active)
    {
        $data = Cache::remember($accident_location_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id, $is_active) {
            $data = $this->accident_location_repository->applyJoins()
                ->where('his_accident_location.id', $id);
            $data = $this->accident_location_repository->applyIsActiveFilter($data, $is_active);
            $data = $data->first();
            return $data;
        });
        return $data;
    }

    public function createAccidentLocation($request, $time, $app_creator, $app_modifier){
        try {
            $data = $this->accident_location_repository->create($request, $time, $app_creator, $app_modifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->accident_location_name));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentLocationIndex($data, $this->accident_location_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function updateAccidentLocation($accident_location_name, $id, $request, $time, $app_modifier){
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->accident_location_repository->getById($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data = $this->accident_location_repository->update($request, $data, $time, $app_modifier);
            // Gọi event để xóa cache
            event(new DeleteCache($accident_location_name));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentLocationIndex($data, $accident_location_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function deleteAccidentLocation($accident_location_name, $id){
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->accident_location_repository->getById($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data = $this->accident_location_repository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($accident_location_name));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $accident_location_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
