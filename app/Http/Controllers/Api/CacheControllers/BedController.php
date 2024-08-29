<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Bed\CreateBedRequest;
use App\Http\Requests\Bed\UpdateBedRequest;
use App\Models\HIS\Bed;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BedService;
use Illuminate\Http\Request;

class BedController extends BaseApiCacheController
{
    protected $bed_service;
    public function __construct(Request $request, ElasticsearchService $elastic_search_service, BedService $bed_service)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elastic_search_service = $elastic_search_service;
        $this->bed_service = $bed_service;
        $this->bed = new Bed();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $this->order_by_join = [
                'bed_type_name',
                'bed_type_code',
                'bed_room_name',
                'bed_room_code',
                'department_name',
                'department_code',
            ];
            $columns = $this->get_columns_table($this->bed);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function bed($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null || $this->elastic_search_type != null) && !$this->cache) {
                if ($this->elastic_search_type != null) {
                    $data = $this->elastic_search_service->handleElasticSearchSearch($this->bed_name)['data'];
                    $count = $this->elastic_search_service->handleElasticSearchSearch($this->bed_name)['count'];
                } else {
                    $data = $this->bed_service->handleDataBaseSearch($keyword, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit)['data'];
                    $count = $this->bed_service->handleDataBaseSearch($keyword, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit)['count'];
                }
            } else {
                if ($id == null) {
                    if($this->elastic){
                        $data = $this->elastic_search_service->handleElasticSearchGetAll($this->bed_name);
                    }else{
                        $data = $this->bed_service->handleDataBaseGetAll($this->bed_name, $this->is_active, $this->order_by, $this->order_by_join, $this->get_all, $this->start, $this->limit);
                    }
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->bed, $this->bed_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    if($this->elastic){
                        $data = $this->elastic_search_service->handleElasticSearchGetWithId($this->bed_name, $id);
                    }else{
                        $data = $this->bed_service->handleDataBaseGetWithId($this->bed_name, $id, $this->is_active);
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
            return return_data_success($param_return, $data ?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    public function bed_create(CreateBedRequest $request)
    {
        return $this->bed_service->createBed($request, $this->time, $this->app_creator, $this->app_modifier);
    }

    public function bed_update(UpdateBedRequest $request, $id)
    {
        return $this->bed_service->updateBed($this->bed_name, $id, $request, $this->time, $this->app_modifier);
    }

    public function bed_delete($id)
    {
        return $this->bed_service->deleteBed($this->bed_name, $id);
    }
}
