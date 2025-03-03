<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\SereServDetailVView;
use Illuminate\Support\Facades\DB;

class SereServDetailVViewRepository
{
    protected $sereServDetailVView;
    public function __construct(SereServDetailVView $sereServDetailVView)
    {
        $this->sereServDetailVView = $sereServDetailVView;
    }

    public function applyJoins()
    {
        return $this->sereServDetailVView
            ->select(
                'v_his_sere_serv_detail.*'
            );
    }
    public function applyWithParam($query)
    {
        return $query->with([
            'exp_mest_medicine', // TH thuốc

            'service_req:id,conclusion_clinical,conclusion_subclinical,conclusion,execute_username,execute_loginname', // XN Xét nghiệm (nếu có ekip thì lấy dựa theo role, nếu k có thì execute_username là người đọc và kỹ thuật viên luôn)
            'service_req.sere_serv_details:id,service_req_id,tdl_service_code,tdl_service_name,is_no_execute', // Dịch vụ gần nhất - dịch vụ trong cùng 1 y lệnh
            'service_req.sere_serv_details.sere_serv_teins:id,sere_serv_id,test_index_id,value,result_code,description,result_description',
            'service_req.sere_serv_details.sere_serv_teins.test_index:id,test_index_code,test_index_name,test_index_unit_id',
            'service_req.sere_serv_details.sere_serv_teins.test_index.test_index_unit:id,test_index_unit_code,test_index_unit_name,test_index_unit_symbol',

            'sere_serv_exts' => function ($query) {
                $query->where('is_delete', 0);
            }, // TT thủ thuật, PT phẫu thuật Chỉ lấy is_delete = 0
            'sere_serv_exts.machine:id,machine_code,machine_name', // Máy trả kết quả CLS
            'sere_serv_exts.film_size:id,film_size_code,film_size_name',
            'service:id,service_code,service_name,icd_cm_id',
            'service.icd_cm:id,icd_cm_code,icd_cm_name', 
            'service.machines:id,machine_code,machine_name', // Máy thực hiện dịch vụ
            'sere_serv_childrens:id,parent_id,service_id,amount', // dịch vụ đính kèm
            'sere_serv_childrens.service:id,service_unit_id,service_code,service_name',
            'sere_serv_childrens.service.service_unit:id,service_unit_code,service_unit_name',

            'ekip_user:id,department_id,ekip_id,loginname,username,execute_role_id', // danh sách ekip
            'ekip_user.execute_role:id,execute_role_code,execute_role_name,is_surgry,is_subclinical,is_subclinical_result', // IS_SURGRY gây mê, IS_SUBCLINICAL là kỹ thuật viên, IS_SUBCLINICAL_RESULT là người đọc kết quả
            'ekip_user.department:id,department_code,department_name',
            'service_req_matys',
            'sere_serv_exts.sar_print', // HA hình ảnh, SA siêu âm, CN thăm dò chức năng, NS nội soi

            'sere_serv_pttts', // PT phẫu thuật
            'sere_serv_pttts.pttt_group:id,pttt_group_code,pttt_group_name', // Phân loại
            'sere_serv_pttts.pttt_method:id,pttt_method_code,pttt_method_name', // Phương pháp pttt
            'sere_serv_pttts.real_pttt_method:id,pttt_method_code,pttt_method_name', // Phương pháp pttt thực tế
            'sere_serv_pttts.pttt_condition:id,pttt_condition_code,pttt_condition_name', // Tình trạng pttt
            'sere_serv_pttts.pttt_catastrophe:id,pttt_catastrophe_code,pttt_catastrophe_name', // Tai biến pttt
            'sere_serv_pttts.pttt_high_tech:id,pttt_high_tech_code,pttt_high_tech_name', // công nghệ cao
            'sere_serv_pttts.pttt_priority:id,pttt_priority_code,pttt_priority_name', // Ưu tiên
            'sere_serv_pttts.pttt_table:id,pttt_table_code,pttt_table_name', // Bàn mổ
            'sere_serv_pttts.emotionless_method:id,emotionless_method_code,emotionless_method_name', // Phương pháp vô cảm
            'sere_serv_pttts.emotionless_method_second:id,emotionless_method_code,emotionless_method_name', // Phương pháp vô cảm 2
            'sere_serv_pttts.emotionless_result:id,emotionless_result_code,emotionless_result_name', // Kết quả vô cảm
            'sere_serv_pttts.death_within:id,death_within_code,death_within_name', // Tử vong trong
            'sere_serv_pttts.blood_abo:id,blood_abo_code', // máu
            'sere_serv_pttts.blood_rh:id,blood_rh_code', // máu

        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_detail.sere_serv_detail_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(v_his_sere_serv_detail.sere_serv_detail_name)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_detail.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_detail.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_sere_serv_detail.' . $key, $item);
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
        return $this->sereServDetailVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->sereServDetailVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'sere_serv_detail_v_view_code' => $request->sere_serv_detail_v_view_code,
    //         'sere_serv_detail_v_view_name' => $request->sere_serv_detail_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_detail_v_view_code' => $request->sere_serv_detail_v_view_code,
    //         'sere_serv_detail_v_view_name' => $request->sere_serv_detail_v_view_name,
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
            $data = $this->applyJoins()->where('v_his_sere_serv_detail.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_sere_serv_detail.id');
            $maxId = $this->applyJoins()->max('v_his_sere_serv_detail.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_detail_v_view', 'v_his_sere_serv_detail', $startId, $endId, $batchSize);
            }
        }
    }
}
