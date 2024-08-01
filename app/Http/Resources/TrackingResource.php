<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
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
            'modifyTime' => $this->modify_time,
            'creator' => $this->creator,
            'modifier' => $this->modifier,
            'appCreator' => $this->app_creator,
            'appModifier' => $this->app_modifier,
            'isActive' => $this->is_active,
            'isDelete' => $this->is_delete,
            'treatmentId' => $this->treatment_id,
            'trackingTime' => $this->tracking_time,
            'icdCode' => $this->icd_code,
            'icdName' => $this->icd_name,
            'departmentId' => $this->department_id,
            'careInstruction' => $this->care_instruction,
            'roomId' => $this->room_id,
            'emrDocumentSttId' => $this->emr_document_stt_id,
            'content' => $this->content,
        ];
    }
}
