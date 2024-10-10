<?php

namespace App\Repositories;

use App\Models\HIS\ServiceReq;
use Illuminate\Support\Facades\DB;

class ServiceReqRepository
{
    protected $serviceReq;
    public function __construct(ServiceReq $serviceReq)
    {
        $this->serviceReq = $serviceReq;
    }

    public function applyJoins()
    {
        return $this->serviceReq
            ->select(
                'his_service_req.*'
            );
    }
    public function lView()
    {
        return $this->serviceReq
            ->select($this->paramSelectLView())
            ->addSelect([
                'order_time' => DB::connection('oracle_his')->raw("
                    (CASE
                        WHEN RESULTING_TIME IS NOT NULL THEN RESULTING_TIME
                        WHEN RESULTING_TIME IS NULL AND SERVICE_REQ_STT_ID = 1 THEN INTRUCTION_TIME
                        ELSE NULL
                    END) AS ORDER_TIME
                ")
            ]);
    }
    public function paramSelectLView(){
        return [
            'id',
            'create_time',          
            'modify_time',      
            'creator',       
            'modifier',      
            'app_creator',       
            'app_modifier',        
            'is_active',         
            'is_delete',        
            'group_code',
            'service_req_code',
            'tdl_patient_code',
            'tdl_patient_name',
            'tdl_patient_gender_name',
            'tdl_patient_dob',
            'tdl_patient_address',
            'treatment_id',
            'tdl_patient_avatar_url',
            'service_req_stt_id',
            'parent_id',
            'execute_room_id',
            'exe_service_module_id',
            'request_department_id',
            'tdl_treatment_code',
            'dhst_id',
            'priority',
            'request_room_id',
            'intruction_time',
            'num_order',
            'service_req_type_id',
            'tdl_hein_card_number',
            'tdl_treatment_type_id',
            'intruction_date',
            'execute_loginname',
            'execute_username',
            'tdl_patient_type_id',
            'is_not_in_debt',
            'is_no_execute',
            'vir_intruction_month',
            'has_child',
            'tdl_patient_phone',
            'resulting_time',
            'tdl_service_ids',
            'call_count',
            'tdl_patient_unsigned_name',
            'start_time',
            'note',
            'tdl_patient_id',
            'icd_code',
            'icd_name',
            'icd_sub_code',
            'icd_text',
            'tdl_is_ksk_approve',
            'tdl_ksk_is_required_approval',
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.SERVICE_REQ_CODE'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyServiceReqSttIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.service_req_stt_id'), $ids);
        }
        return $query;
    }
    public function applyNotInServiceReqTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereNotIn(DB::connection('oracle_his')->raw('his_service_req.service_req_type_id'), $ids);
        }
        return $query;
    }
    public function applyTdlPatientTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_type_id'), $ids);
        }
        return $query;
    }
    public function applyExecuteRoomIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.execute_room_id'), $id);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $time)
    {
        if ($time !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '>=', $time);
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $time)
    {
        if ($time !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '<=', $time);
        }
        return $query;
    }
    public function applyHasExecuteFilter($query, $hasExecute)
    {
        if (!$hasExecute) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.is_no_execute'), 1);
        }
        return $query;
    }
    public function applyIsNotKskRequriedAprovalOrIsKskApproveFilter($query, $has)
    {
        if ($has) {
            $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_KSK_IS_REQUIRED_APPROVAL'), null);
            $query = $query->orwhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 1);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_service_req.' . $key, $item);
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
        return $this->serviceReq->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->serviceReq::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'service_req_code' => $request->service_req_code,
    //         'service_req_name' => $request->service_req_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'service_req_code' => $request->service_req_code,
    //         'service_req_name' => $request->service_req_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($callback, $batchSize = 5000, $id = null)
    {
        $query  = $this->lView();
        if ($id != null) {
            $data = $query ->where('his_service_req.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data ;
            }
        } else {
            $batchData = [];
            $count = 0;
            foreach ($query->cursor() as $item) {
                $attributes = $item->getAttributes();
                $batchData[] = $attributes;
                $count++;
    
                if ($count % $batchSize == 0) {
                    $callback($batchData);
                    $batchData = [];
                }
            }
            if (!empty($batchData)) {
                $callback($batchData);
            }
        }
    }
}
