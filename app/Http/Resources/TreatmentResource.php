<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentResource extends JsonResource
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
            'createTime' => $this->create_time,
            'treatmentCode' => $this->treatment_code,
            'tdlPatientCode' => $this->tdl_patient_code,
            'tdlPatientName' => $this->tdl_patient_name,
            'tdlPatientDob' => $this->tdl_patient_dob,
            'tdlPatientGenderName' => $this->tdl_patient_gender_name,
            'icdCode' => $this->icd_code,
            'icdName' => $this->icd_name,
            'icdSubCode' => $this->icd_sub_code,
            'icdText' => $this->icd_text,
            'inTime' => $this->in_time,
            'isActive' => $this->is_active,
            'inDate' => $this->in_date,
        ];
    }
}
