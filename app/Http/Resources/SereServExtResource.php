<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServExtResource extends JsonResource
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
            'sereServId' => $this->sere_serv_id,
            'conclude' => $this->conclude,
            'jsonPrintId' => $this->json_print_id,
            'descriptionSarPrintId' => $this->description_sar_print_id,
            'machineCode' => $this->machine_code,
            'machineId' => $this->machine_id,
            'numberOfFilm' => $this->number_of_film,
            'beginTime' => $this->begin_time,
            'endTime' => $this->end_time,
            'tdlServiceReqId' => $this->tdl_service_req_id,
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'filmSizeId' => $this->film_size_id,
            'subclinicalPresUsername' => $this->subclinical_pres_username,
            'subclinicalPresLoginname' => $this->subclinical_pres_loginname,
            'subclinicalResultUsername' => $this->subclinical_result_username,
            'subclinicalResultLoginname' => $this->subclinical_result_loginname,
            'subclinicalPresId' => $this->subclinical_pres_id,
            'description' => $this->description,
        ];
    }
}
