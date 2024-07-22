<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebateResource extends JsonResource
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
            'icdCode' => $this->icd_code,
            'icdName' => $this->icd_name,
            'icdSubCode' => $this->icd_sub_code,
            'icdText' => $this->icd_text,
            'debateTime' => $this->debate_time,
            'requestLoginname' => $this->request_loginname,
            'requestUsername' => $this->request_username,
            'treatmentTracking' => $this->treatment_tracking,
            'treatmentFromTime' => $this->treatment_from_time,
            'treatmentMethod' => $this->treatment_method,
            'location' => $this->location,
            'pathologicalHistory' => $this->pathological_history,
            'hospitalizationState' => $this->hospitalization_state,
            'beforeDiagnostic' => $this->before_diagnostic,
            'diagnostic' => $this->diagnostic,
            'careMethod' => $this->care_method,
            'conclusion' =>$this->conclusion,
            'discussion' => $this->discussion,
            'medicineUseFormName' => $this->medicine_use_form_name,
            'medicineTypeName' => $this->medicine_type_name,
            'treatmentId' => $this->treatment_id,
            'icdIdDelete' => $this->icd_id__delete,
            'debateTypeId' => $this->debate_type_id,
            'departmentId' => $this->department_id,
            'surgeryServiceId' => $this->surgery_service_id,
            'emotionlessMethodId' => $this->emotionless_method_id,
            'ptttMethodId' => $this->pttt_method_id,
            'trackingId' => $this->tracking_id,
            'serviceId' => $this->service_id,
            'debateReasonId' => $this->debate_reason_id,
            'medicineTypeIds' => $this->medicine_type_ids,
            'activeIngredientIds' => $this->active_ingredient_ids,
        ];
    }
}
