<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\MedicineListVView;
use Illuminate\Support\Facades\DB;

class MedicineListVViewRepository
{
    protected $medicineListVView;
    public function __construct(MedicineListVView $medicineListVView)
    {
        $this->medicineListVView = $medicineListVView;
    }

    public function applyJoins()
    {
        return $this->medicineListVView
            ->select([
                'id',
                'tdl_service_name',
                'tdl_service_code',
                'active_ingr_bhyt_name',
                'concentra',
                'amount',
                'package_number',
                'medicine_use_form_code',
                'medicine_use_form_name',
                'service_unit_code',
                'service_unit_name',
                'intruction_date',
                'intruction_time',
                'tutorial'
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('medicine_list_code'), 'like', '%'. $keyword . '%')
            ->orWhere(('lower(medicine_list_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyServiceTypeCodeTHFilter($query)
    {
        $query->where(('service_type_code'), 'TH');
        return $query;
    }
    public function applyPatientCodeFilter($query, $param)
    {
        if($param != null){
            $query->where(('tdl_patient_code'), $param);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if($param != null){
            $query->where(('intruction_time'), '>=', $param);
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if($param != null){
            $query->where(('intruction_time'), '<=', $param);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        if ($param != null) {
            switch ($param) {
                case 'selectDichTruyen':
                    $query->where('medicine_use_form_code', '=', '2.15');
                    return $query;
                case 'selectThuocPha':
                    $query->where(function ($q) {
                        $q->where('medicine_use_form_code', 'like', '2.%')
                          ->orWhere('medicine_use_form_code', 'like', '1.%');
                    });
                    return $query;
                default:
                    return $query;
            }
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
        return $this->medicineListVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->medicineListVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'medicine_list_v_view_code' => $request->medicine_list_v_view_code,
    //         'medicine_list_v_view_name' => $request->medicine_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'medicine_list_v_view_code' => $request->medicine_list_v_view_code,
    //         'medicine_list_v_view_name' => $request->medicine_list_v_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('medicine_list_v_view', 'v_his_medicine_list', $startId, $endId, $batchSize);
            }
        }
    }
}