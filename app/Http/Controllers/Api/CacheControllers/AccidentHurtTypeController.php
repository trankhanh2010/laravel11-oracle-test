<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccidentHurtType\CreateAccidentHurtTypeRequest;
use App\Http\Requests\AccidentHurtType\UpdateAccidentHurtTypeRequest;
use App\Models\HIS\AccidentHurtType;
use Illuminate\Http\Request;

use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccidentHurtTypeService;
class AccidentHurtTypeController extends BaseApiCacheController
{
    protected $accident_hurt_type_service;
    public function __construct(Request $request, ElasticsearchService $elastic_search_service, AccidentHurtTypeService $accident_hurt_type_service)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elastic_search_service = $elastic_search_service;
        $this->accident_hurt_type_service = $accident_hurt_type_service;
        $this->accident_hurt_type = new AccidentHurtType();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $this->order_by_join = [];
            $columns = $this->get_columns_table($this->accident_hurt_type);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function accident_hurt_type($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null || $this->elastic_search_type != null) && !$this->cache) {
                if ($this->elastic_search_type != null) {
                    $data = $this->elastic_search_service->handleElasticSearchSearch($this->accident_hurt_type_name);
                    $count = $data['count'];
                    $data = $data['data'];
                } else {
                    $data = $this->accident_hurt_type_service->handleDataBaseSearch($keyword, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit);
                    $count = $data['count'];
                    $data = $data['data'];
                }
            } else {
                if ($id == null) {
                    if($this->elastic){
                        $data = $this->elastic_search_service->handleElasticSearchGetAll($this->accident_hurt_type_name);
                    }else{
                        $data = $this->accident_hurt_type_service->handleDataBaseGetAll($this->accident_hurt_type_name, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit);
                    }
                } else {
                    if ($id !== null) {
                        $validationError = $this->validateAndCheckId($id, $this->accident_hurt_type, $this->accident_hurt_type_name);
                        if ($validationError) {
                            return $validationError;
                        }
                    }
                    if($this->elastic){
                        $data = $this->elastic_search_service->handleElasticSearchGetWithId($this->accident_hurt_type_name, $id);
                    }else{
                        $data = $this->accident_hurt_type_service->handleDataBaseGetWithId($this->accident_hurt_type_name, $id, $this->is_active);
                    }
                }
            }
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => $count ?? ($data['count'] ?? null),
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
    public function accident_hurt_type_create(CreateAccidentHurtTypeRequest $request)
    {
        return $this->accident_hurt_type_service->createAccidentHurtType($request, $this->time, $this->app_creator, $this->app_modifier);
    }

    public function accident_hurt_type_update(UpdateAccidentHurtTypeRequest $request, $id)
    {
        return $this->accident_hurt_type_service->updateAccidentHurtType($this->accident_hurt_type_name, $id, $request, $this->time, $this->app_modifier);
    }

    public function accident_hurt_type_delete($id)
    {
        return $this->accident_hurt_type_service->deleteAccidentHurtType($this->accident_hurt_type_name, $id);

    }
}
