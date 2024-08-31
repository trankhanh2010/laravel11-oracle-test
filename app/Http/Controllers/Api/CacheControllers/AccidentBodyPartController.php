<?php

namespace App\Http\Controllers\Api\CacheControllers;


use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccidentBodyPart\CreateAccidentBodyPartRequest;
use App\Http\Requests\AccidentBodyPart\UpdateAccidentBodyPartRequest;
use App\Models\HIS\AccidentBodyPart;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccidentBodyPartService;
use Illuminate\Http\Request;


class AccidentBodyPartController extends BaseApiCacheController
{
    protected $accident_body_part_service;
    public function __construct(Request $request, ElasticsearchService $elastic_search_service, AccidentBodyPartService $accident_body_part_service)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elastic_search_service = $elastic_search_service;
        $this->accident_body_part_service = $accident_body_part_service;
        $this->accident_body_part = new AccidentBodyPart();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $this->order_by_join = [];
            $columns = $this->get_columns_table($this->accident_body_part);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }

    public function accident_body_part($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null || $this->elastic_search_type != null) && !$this->cache) {
                if ($this->elastic_search_type != null) {
                    $data = $this->elastic_search_service->handleElasticSearchSearch($this->accident_body_part_name);
                    $count = $data['count'];
                    $data = $data['data'];
                } else {
                    $data = $this->accident_body_part_service->handleDataBaseSearch($keyword, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit);
                    $count = $data['count'];
                    $data = $data['data'];
                }
            } else {
                if ($id == null) {
                    if ($this->elastic) {
                        $data = $this->elastic_search_service->handleElasticSearchGetAll($this->accident_body_part_name);
                    } else {
                        $data = $this->accident_body_part_service->handleDataBaseGetAll($this->accident_body_part_name, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit);
                    }
                } else {
                    if ($id !== null) {
                        $validationError = $this->validateAndCheckId($id, $this->accident_body_part, $this->accident_body_part_name);
                        if ($validationError) {
                            return $validationError;
                        }
                    }
                    if ($this->elastic) {
                        $data = $this->elastic_search_service->handleElasticSearchGetWithId($this->accident_body_part_name, $id);
                    } else {
                        $data = $this->accident_body_part_service->handleDataBaseGetWithId($this->accident_body_part_name, $id, $this->is_active);
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
    public function accident_body_part_create(CreateAccidentBodyPartRequest $request)
    {
        try {
            return $this->accident_body_part_service->createAccidentBodyPart($request, $this->time, $this->app_creator, $this->app_modifier);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function accident_body_part_update(UpdateAccidentBodyPartRequest $request, $id)
    {
        try {
            return $this->accident_body_part_service->updateAccidentBodyPart($this->accident_body_part_name, $id, $request, $this->time, $this->app_modifier);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function accident_body_part_delete(Request $request, $id)
    {
        try {
            return $this->accident_body_part_service->deleteAccidentBodyPart($this->accident_body_part_name, $id);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
}
