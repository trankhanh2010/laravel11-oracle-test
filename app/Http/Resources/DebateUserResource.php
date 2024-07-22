<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebateUserResource extends JsonResource
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
            'groupCode' => $this->group_code ,
            'debateId' => $this->debate_id ,
            'loginname' => $this->loginname ,
            'username' => $this->username ,
            'isPresident' => $this->is_president ,
            'isSecretary' => $this->is_secretary ,
            'description' => $this->description ,
            'debateTempId' => $this->debate_temp_id ,
            'executeRoleId' => $this->execute_role_id ,
            'isOutOfHospital' => $this->is_out_of_hospital ,
        ];
    }
}
