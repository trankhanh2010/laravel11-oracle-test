<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentBedRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treatmentId' => $this->treatment_id,
            'coTreatmentId' => $this->co_treatment_id,
            'addTime' => $this->add_time,
            'removeTime' => $this->remove_time,
            'bedRoomId' => $this->bed_room_id,

            'tdlPatientFirstName' => $this->tdl_patient_first_name,
            'treatmentCode' => $this->treatment_code,
            'tdlPatientLastName' => $this->tdl_patient_last_name,
            'tdlPatientName' => $this->tdl_patient_name,
            'tdlPatientDob' => $this->tdl_patient_dob,
            'tdlPatientGenderName' => $this->tdl_patient_gender_name,
            'tdlPatientCode' => $this->tdl_patient_code,
            'tdlPatientAddress' => $this->tdl_patient_address,
            'tdlHeinCardNumber' => $this->tdl_hein_card_number,
            'tdlHeinMediOrgCode' => $this->tdl_hein_medi_org_code,
            'icdCode' => $this->icd_code,
            'icdName' => $this->icd_name,
            'icdText' => $this->icd_text,
            'icdSubCode' => $this->icd_sub_code,
            'tdlPatientGenderId' => $this->tdl_patient_gender_id,
            'tdlHeinMediOrgName' => $this->tdl_hein_medi_org_name,
            'tdlTreatmentTypeId' => $this->tdl_treatment_type_id,
            'emrCoverTypeId' => $this->emr_cover_type_id,
            'clinicalInTime' => $this->clinical_in_time,
            'coTreatDepartmentIds' => $this->co_treat_department_ids,
            'lastDepartmentId' => $this->last_department_id,
            'tdlPatientUnsignedName' => $this->tdl_patient_unsigned_name,
            'treatmentMethod' => $this->treatment_method,
            'tdlHeinCardFromTime' => $this->tdl_hein_card_from_time,
            'tdlHeinCardToTime' => $this->tdl_hein_card_to_time,

            'patientTypeCode' => $this->patient_type_code,
            'patientTypeName' => $this->patient_type_name,

            'departmentCode' => $this->department_code,
            'departmentName' => $this->department_name,

            'note' => $this->note,

            'bedRoomName' => $this->bed_room_name,
        ];
    }
}
