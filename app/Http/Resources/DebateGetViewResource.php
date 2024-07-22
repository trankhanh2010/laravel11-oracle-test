<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebateGetViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'createTime' => $this->create_time,
            'modifyTime' => $this->modify_time,
            'creator' => $this->creator,
            'modifier' => $this->modifier,
            'appCreator' => $this->app_creator,
            'appModifier' => $this->app_modifier,
            'isActive' => $this->is_active,
            'isDelete' => $this->is_delete,
            'treatmentId' => $this->treatment_id,
            'icdCode' => $this->icd_code,
            'icdName' => $this->icd_name,
            'icdSubCode' => $this->icd_sub_code,
            'icdText' => $this->icd_text,
            'departmentId' => $this->department_id,
            'debateTime' => $this->debate_time,
            'requestLoginname' => $this->request_loginname,
            'requestUsername' => $this->request_username,
            'treatmentTracking' => $this->treatment_tracking,
            'treatmentFromTime' => $this->treatment_from_time,
            'treatmentToTime' => $this->treatment_to_time,
            'location' => $this->location,
            'conclusion' =>$this->conclusion,
            'debateTypeId' => $this->debate_type_id,
            'contentType' => $this->content_type,
            'subclinicalProcesses' => $this->subclinical_processes,
            'emotionlessMethodId' => $this->emotionless_method_id,
            'surgeryTime' => $this->surgery_time,
            'ptttMethodId' => $this->pttt_method_id,

            'patientId' => $this->patient_id,
            'treatmentCode' => $this->treatment_code,
            'tdlPatientFirstName' => $this->tdl_patient_first_name,
            'tdlPatientLastName' => $this->tdl_patient_last_name,
            'tdlPatientName' => $this->tdl_patient_name,
            'tdlPatientDob' => $this->tdl_patient_dob,
            'tdlPatientAddress' => $this->tdl_patient_address,
            'tdlPatientGenderName' => $this->tdl_patient_gender_name,

            'departmentCode' => $this->department_code,
            'departmentName' => $this->department_name,

            'debateTypeCode' => $this->debate_type_code,
            'debateTypeName' => $this->debate_type_name,

            'emotionlessMethodCode' => $this->emotionless_method_code,
            'emotionlessMethodName' => $this->emotionless_method_name,

            'debateReasonCode' => $this->debate_reason_code,
            'debateReasonName' => $this->debate_reason_name,

            
        ];
    }
}
