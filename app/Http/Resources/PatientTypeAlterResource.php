<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientTypeAlterResource extends JsonResource
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
            'departmentTranId' => $this->department_tran_id,
            'treatmentTypeId' => $this->treatment_type_id,
            'patientTypeId' => $this->patient_type_id,
            'logTime' => $this->log_time,
            'treatmentId' => $this->treatment_id,
            'tdlPatientId' => $this->tdl_patient_id,
            'executeRoomId' => $this->execute_room_id,
            'levelCode' => $this->level_code,
            'rightRouteCode' => $this->right_route_code,
            'rightRouteTypeCode' => $this->right_route_type_code,
            'heinMediOrgCode' => $this->hein_medi_org_code,
            'heinMediOrgName' => $this->hein_medi_org_name,
            'hasBirthCertificate' => $this->has_birth_certificate,
            'heinCardNumber' => $this->hein_card_number,
            'heinCardFromTime' => $this->hein_card_from_time,
            'heinCardToTime' => $this->hein_card_to_time,
            'address' => $this->address,
            'join5Year' => $this->join_5_year,
            'paid6Month' => $this->paid_6_month,
            'primaryPatientTypeId' => $this->primary_patient_type_id,
            'patientTypeCode' => $this->patient_type_code,
            'patientTypeName' => $this->patient_type_name,
            'isCopayment' => $this->is_copayment,
            'treatmentTypeCode' => $this->treatment_type_code,
            'treatmentTypeName' => $this->treatment_type_name,
            'heinTreatmentTypeCode' => $this->hein_treatment_type_code,
        ];
    }
}
