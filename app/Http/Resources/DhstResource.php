<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DhstResource extends JsonResource
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
            'executeRoomId' => $this->execute_room_id,
            'executeLoginname' => $this->execute_loginname,
            'executeUsername' => $this->execute_username,
            'executeTime' => $this->execute_time,
            'temperature' => $this->temperature,
            'breathRate' => $this->breath_rate,
            'weight' => $this->weight,
            'height' => $this->height,
            'bloodPressureMax' => $this->blood_pressure_max,
            'bloodPressureMin' => $this->blood_pressure_min,
            'pulse' => $this->pulse,
            'virBmi' => $this->vir_bmi,
            'virBodySurfaceArea' => $this->vir_body_surface_area,
        ];
    }
}
