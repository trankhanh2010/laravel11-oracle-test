<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServTeinGetViewResource extends JsonResource
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
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'machineId' => $this->machine_id,
            'note' => $this->note,
            'leaven' => $this->leaven,
            'tdlServiceReqId' => $this->tdl_service_req_id,

            'machineGroupCode' => $this->machine_group_code,
            'sourceCode' => $this->source_code,
            'serialNumber' => $this->serial_number,
            'machineName' => $this->machine_name,
            'machineCode' => $this->machine_code,

            'testIndexUnitId' => $this->test_index_unit_id,
            'testIndexName' => $this->test_index_name,
            'testIndexCode' => $this->test_index_code,
            'isNotShowService' => $this->is_not_show_service,

            'testIndexUnitCode' => $this->test_index_unit_code,
            'testIndexUnitName' => $this->test_index_unit_name,

        ];
    }
}
