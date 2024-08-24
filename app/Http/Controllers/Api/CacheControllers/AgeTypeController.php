<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Resources\Elastic\ElasticResource;
use App\Models\HIS\AgeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AgeTypeController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->age_type = new AgeType();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $this->order_by_join = [];
            $columns = $this->get_columns_table($this->age_type);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    protected function applyJoins()
    {
        return $this->age_type
            ->select(
                'his_age_type.*',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_age_type.age_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_age_type.age_type_name'), 'like', $keyword . '%');
        });
    }
    protected function applyIsActiveFilter($query)
    {
        if ($this->is_active !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_age_type.is_active'), $this->is_active);
        }

        return $query;
    }
    protected function applyOrdering($query)
    {
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (in_array($key, $this->order_by_join)) {

                } else {
                    $query->orderBy('his_age_type.' . $key, $item);
                }
            }
        }

        return $query;
    }
    protected function fetchData($query)
    {
        if ($this->get_all) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        }
    }
    protected function buildSearchBody()
    {
        $query = $this->buildSearchQuery($this->elastic_search_type, $this->elastic_field, $this->keyword, $this->age_type_name);
        $highlight = $this->buildHighlight($this->elastic_search_type);
        $paginate = $this->buildPaginateElastic();

        $body = [
            'query' => $query,
            'highlight' => $highlight,
        ];
        $body = array_merge($body, $paginate);

        if ($this->order_by_elastic !== null) {
            $body['sort'] = $this->buildSort($this->age_type_name);
        }

        return $body;
    }
    protected function executeSearch($index, $body)
    {
        return $this->client->search([
            'index' => $index,
            'body' => $body,
        ]);
    }
    public function age_type($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null || $this->elastic_search_type != null) {
                if ($this->elastic_search_type != null) {
                    $body = $this->buildSearchBody();
                    $data = $this->executeSearch($this->age_type_name, $body);
                    $count = $data['hits']['total']['value'];
                    $data = ElasticResource::collection($data['hits']['hits']);
                } else {
                    $data = $this->applyJoins();
                    $data = $this->applyKeywordFilter($data, $keyword);
                    $data = $this->applyIsActiveFilter($data);
                    $count = $data->count();
                    $data = $this->applyOrdering($data);
                    $data = $this->fetchData($data);
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->age_type_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->applyJoins();
                        $data = $this->applyIsActiveFilter($data);
                        $count = $data->count();
                        $data = $this->applyOrdering($data);
                        $data = $this->fetchData($data);
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->age_type, $this->age_type_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->age_type_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->applyJoins()
                        ->where('his_age_type.id', $id);
                        $data = $this->applyIsActiveFilter($data);
                        $data = $data->first();
                        return $data;
                    });
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
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
