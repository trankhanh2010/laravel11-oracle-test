<?php
namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AccidentHurtTypeRepository;

class AccidentHurtTypeService extends BaseApiCacheController
{
    protected $accident_hurt_type_repository;
    public function __construct(Request $request, AccidentHurtTypeRepository $accident_hurt_type_repository)
    {
        parent::__construct($request);
        $this->accident_hurt_type_repository = $accident_hurt_type_repository;
    }

    public function handleDataBaseSearch($keyword, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        $data = $this->accident_hurt_type_repository->applyJoins();
        $data = $this->accident_hurt_type_repository->applyKeywordFilter($data, $keyword);
        $data = $this->accident_hurt_type_repository->applyIsActiveFilter($data, $is_active);
        $count = $data->count();
        $data = $this->accident_hurt_type_repository->applyOrdering($data, $order_by, $order_by_join);
        $data = $this->accident_hurt_type_repository->fetchData($data, $get_all, $start, $limit);
        return ['data' => $data, 'count' => $count];
    }
    public function handleDataBaseGetAll($accident_hurt_type_name, $is_active, $order_by, $order_by_join, $get_all, $start, $limit)
    {
        $data = Cache::remember($accident_hurt_type_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () use ($is_active, $order_by, $order_by_join, $get_all, $start, $limit) {
            $data = $this->accident_hurt_type_repository->applyJoins();
            $data = $this->accident_hurt_type_repository->applyIsActiveFilter($data, $is_active);
            $count = $data->count();
            $data = $this->accident_hurt_type_repository->applyOrdering($data, $order_by, $order_by_join);
            $data = $this->accident_hurt_type_repository->fetchData($data, $get_all, $start, $limit);
            return ['data' => $data, 'count' => $count];
        });
        return $data;
    }
    public function handleDataBaseGetWithId($accident_hurt_type_name, $id, $is_active)
    {
        $data = Cache::remember($accident_hurt_type_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id, $is_active) {
            $data = $this->accident_hurt_type_repository->applyJoins()
                ->where('his_accident_hurt_type.id', $id);
            $data = $this->accident_hurt_type_repository->applyIsActiveFilter($data, $is_active);
            $data = $data->first();
            return $data;
        });
        return $data;
    }

    public function createAccidentHurtType($request, $time, $app_creator, $app_modifier){
        try {
            $data = $this->accident_hurt_type_repository->create($request, $time, $app_creator, $app_modifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->accident_hurt_type_name));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentHurtTypeIndex($data, $this->accident_hurt_type_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }

    public function updateAccidentHurtType($accident_hurt_type_name, $id, $request, $time, $app_modifier){
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->accident_hurt_type_repository->getById($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data = $this->accident_hurt_type_repository->update($request, $data, $time, $app_modifier);
            // Gọi event để xóa cache
            event(new DeleteCache($accident_hurt_type_name));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentHurtTypeIndex($data, $accident_hurt_type_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }

    public function deleteAccidentHurtType($accident_hurt_type_name, $id){
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->accident_hurt_type_repository->getById($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data = $this->accident_hurt_type_repository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($accident_hurt_type_name));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $accident_hurt_type_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
