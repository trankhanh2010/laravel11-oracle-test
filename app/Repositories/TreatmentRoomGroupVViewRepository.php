<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TreatmentRoomGroupVView;
use Illuminate\Support\Facades\DB;

class TreatmentRoomGroupVViewRepository
{
    protected $treatmentRoomGroupVView;
    public function __construct(TreatmentRoomGroupVView $treatmentRoomGroupVView)
    {
        $this->treatmentRoomGroupVView = $treatmentRoomGroupVView;
    }

    public function applyJoins()
    {
        return $this->treatmentRoomGroupVView
            ->select();
    }
    public function applyDepartmentCodeFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('department_code'), $param);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('' . $key, $item);
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
        return $this->treatmentRoomGroupVView->find($id);
    }

}