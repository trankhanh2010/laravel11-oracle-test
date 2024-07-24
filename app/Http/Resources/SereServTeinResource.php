<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServTeinResource extends JsonResource
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
            'sereServId' => $this->sere_serv_id,
            'testIndexId' => $this->test_index_id,
            'value' => $this->value,
            'resultCode' => $this->result_code,
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'tdlServiceReqId' => $this->tdl_service_req_id,
            'resultDescription' => $this->result_description,

        ];
    }
}
