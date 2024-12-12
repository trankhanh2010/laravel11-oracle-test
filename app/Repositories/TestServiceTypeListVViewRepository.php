<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TestServiceTypeListVView;
use Illuminate\Support\Facades\DB;

class TestServiceTypeListVViewRepository
{
    protected $testServiceTypeListVView;
    public function __construct(TestServiceTypeListVView $testServiceTypeListVView)
    {
        $this->testServiceTypeListVView = $testServiceTypeListVView;
    }

    public function applyJoins()
    {
        return $this->testServiceTypeListVView
            ->select(
                'v_his_test_service_type_list.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_type_list.test_service_type_list_v_view_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('v_his_test_service_type_list.test_service_type_list_v_view_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_type_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyPatientIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_type_list.tdl_patient_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_test_service_type_list.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->testServiceTypeListVView->find($id);
    }
}