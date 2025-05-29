<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PatientTypeAlter;
use Illuminate\Support\Facades\DB;

class PatientTypeAlterRepository
{
    protected $patientTypeAlter;
    public function __construct(PatientTypeAlter $patientTypeAlter)
    {
        $this->patientTypeAlter = $patientTypeAlter;
    }

    public function applyJoins()
    {
        return $this->patientTypeAlter
            ->select(
                'his_patient_type_alter.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.patient_type_alter_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_patient_type_alter.patient_type_alter_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_patient_type_alter.' . $key, $item);
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
    public function getJsonByHeinCardNumberAndPatientTypeId($heinCardNumber, $patientTypeId, $treatmentId)
    {
        $data = $this->patientTypeAlter
            ->where('hein_card_number', $heinCardNumber)
            ->where('patient_type_id', $patientTypeId)
            ->where('treatment_id', $treatmentId)
            ->orderBy('log_time', 'desc')
            ->first();
        if ($data) {
            $dataArrayJson = [
                "ID" => $data->id,
                "CREATE_TIME" => null,
                "MODIFY_TIME" => null,
                "CREATOR" => null,
                "MODIFIER" => null,
                "APP_CREATOR" => null,
                "APP_MODIFIER" => null,
                "IS_ACTIVE" => (int) $data->is_active,
                "IS_DELETE" => (int)  $data->is_delete,
                "GROUP_CODE" => $data->group_code,
                "DEPARTMENT_TRAN_ID" => $data->department_tran_id ? (int) $data->department_tran_id : $data->department_tran_id,
                "TREATMENT_TYPE_ID" => $data->treatment_type_id ? (int) $data->treatment_type_id : $data->treatment_type_id,
                "PATIENT_TYPE_ID" => $data->patient_type_id ? (int) $data->patient_type_id : $data->patient_type_id,
                "LOG_TIME" => $data->log_time ? (int) $data->log_time : $data->log_time,
                "TREATMENT_ID" => $data->treatment_id ? (int) $data->treatment_id : $data->treatment_id,
                "TDL_PATIENT_ID" => $data->tdl_patient_id ? (int) $data->tdl_patient_id : $data->tdl_patient_id,
                "EXECUTE_ROOM_ID" => $data->execute_room_id ? (int) $data->execute_room_id : $data->execute_room_id,
                "EXECUTE_LOGINNAME" => $data->execute_loginname,
                "EXECUTE_USERNAME" => $data->execute_username,
                "LEVEL_CODE" => $data->level_code,
                "RIGHT_ROUTE_CODE" => $data->right_route_code,
                "RIGHT_ROUTE_TYPE_CODE" => $data->right_route_type_code,
                "LIVE_AREA_CODE" => $data->live_area_code,
                "HEIN_MEDI_ORG_CODE" => $data->hein_medi_org_code,
                "HEIN_MEDI_ORG_NAME" => $data->hein_medi_org_name,
                "HAS_BIRTH_CERTIFICATE" => $data->has_birth_certificate,
                "HEIN_CARD_NUMBER" => $data->hein_card_number,
                "HEIN_CARD_FROM_TIME" => $data->hein_card_from_time ? (int) $data->hein_card_from_time : $data->hein_card_from_time,
                "HEIN_CARD_TO_TIME" => $data->hein_card_to_time ? (int) $data->hein_card_to_time : $data->hein_card_to_time,
                "ADDRESS" => $data->address,
                "HNCODE" => $data->hncode,
                "JOIN_5_YEAR" => $data->join_5_year,
                "PAID_6_MONTH" => $data->paid_6_month,
                "IS_NO_CHECK_EXPIRE" => $data->is_no_check_expire ? (int) $data->is_no_check_expire : $data->is_no_check_expire,
                "FREE_CO_PAID_TIME" => $data->free_co_paid_time ? (int) $data->free_co_paid_time : $data->free_co_paid_time,
                "IS_TEMP_QN" => $data->is_temp_qn ? (int) $data->is_temp_qn : $data->is_temp_qn,
                "BHYT_URL" => $data->bhyt_url,
                "KSK_CONTRACT_ID" => $data->ksk_contract_id ? (int) $data->ksk_contract_id : $data->ksk_contract_id,
                "JOIN_5_YEAR_TIME" => $data->join_5_year_time ? (int) $data->join_5_year_time : $data->join_5_year_time,
                "PRIMARY_PATIENT_TYPE_ID" => $data->primary_patient_type_id ? (int) $data->primary_patient_type_id : $data->primary_patient_type_id,
                "HEIN_CARD_TO_TIME_TMP" => $data->hein_card_to_time_tmp,
                "HAS_WORKING_LETTER" => $data->has_working_letter,
                "HAS_ABSENT_LETTER" => $data->has_absent_letter,
                "IS_TT46" => $data->is_tt46 ? (int) $data->is_tt46 : $data->is_tt46,
                "TT46_NOTE" => $data->tt46_note ? (int) $data->tt46_note : $data->tt46_note,
                "GUARANTEE_LOGINNAME" => $data->guarantee_loginname,
                "GUARANTEE_USERNAME" => $data->guarantee_username,
                "GUARANTEE_REASON" => $data->guarantee_reason,
                "IS_NEWBORN" => $data->is_newborn ? (int) $data->is_newborn : $data->is_newborn,
                "HIS_DEPARTMENT_TRAN" => $data->his_department_tran,
                "HIS_KSK_CONTRACT" => $data->his_ksk_contract,
                "HIS_PATIENT_TYPE" => $data->his_patient_type,
                "HIS_PATIENT_TYPE1" => $data->his_patient_type1,
                "HIS_TREATMENT_TYPE" => $data->his_treatment_type,
                "HIS_TREATMENT" => $data->his_treatment
            ];
        }else{
            return;
        }
        return json_encode($dataArrayJson, JSON_UNESCAPED_UNICODE);
    }
    public function getById($id)
    {
        return $this->patientTypeAlter->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->patientTypeAlter::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'patient_type_alter_code' => $request->patient_type_alter_code,
            'patient_type_alter_name' => $request->patient_type_alter_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'patient_type_alter_code' => $request->patient_type_alter_code,
            'patient_type_alter_name' => $request->patient_type_alter_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_patient_type_alter.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_patient_type_alter.id');
            $maxId = $this->applyJoins()->max('his_patient_type_alter.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('patient_type_alter', 'his_patient_type_alter', $startId, $endId, $batchSize);
            }
        }
    }
}
