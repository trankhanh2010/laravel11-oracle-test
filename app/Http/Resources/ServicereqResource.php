<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicereqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'serviceReqCode' => $this->service_req_code, 
            'tdlPatientCode'  => $this->tdl_patient_code,
            'tdlPatientName'  => $this->tdl_patient_name,
            'tdlPatientGenderName'  => $this->tdl_patient_gender_name,
            'tdlPatientDob'  => $this->tdl_patient_dob,
            'treatmentId' => $this->treatment_id,
            'tdlPatientAvatarUrl' => $this->tdl_patient_avatar_url,
            'serviceReqSttId' => $this->service_req_stt_id,
            'parentId' => $this->parent_id,
            'executeRoomId' => $this->execute_room_id,
            'exeServiceModuleId' => $this->exe_service_module_id,
            'requestDepartmentId' => $this->request_department_id,
            'tdlTreatmentCode' => $this->tdl_treatment_code,
            'dhstId' => $this->dhst_id,
            'priority' => $this->priority,
            'requestRoomId' => $this->request_room_id,
            'intructionTime' => $this->intruction_time,
            'numOrder' => $this->num_order,
            'serviceReqTypeId' => $this->service_req_type_id,
            'tdlHeinCardNumber' => $this->tdl_hein_card_number,
            'tdlTreatmentTypeId' => $this->tdl_treatment_type_id,
            'intructionDate' => $this->intruction_date,
            'executeLoginname' => $this->execute_loginname,
            'executeUsername' => $this->execute_username,
            'tdlPatientTypeId' => $this->tdl_patient_type_id,
            'isNotInDebt' => $this->is_not_in_debt,
            'isNoExecute' => $this->is_no_execute,
            'virIntructionMonth' => $this->vir_intruction_month,
            'hasChild' => $this->has_child,
            'tdlPatientPhone' => $this->tdl_patient_phone,
            'resultingTime' => $this->resulting_time,
            'tdlServiceIds' => $this->tdl_service_ids,
            'callCount' => $this->call_count,
            'tdlPatientUnsignedName' => $this->tdl_patient_unsigned_name,
            'startTime' => $this->start_time,
            'note' => $this->note,
            'tdlPatientId' => $this->tdl_patient_id,
            'icdCode' => $this->icd_code,
            'icdName' => $this->icd_name,
            'icdSubCode' => $this->icd_sub_code,
            'icdText' => $this->icd_text,
        ];
    }
}
