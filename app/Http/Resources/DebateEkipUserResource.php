<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebateEkipUserResource extends JsonResource
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
            'groupCode' => $this->group_code,
            'debateId' => $this->debate_id,
            'loginname' => $this->loginname,
            'username' => $this->username,
            'executeRoleId' => $this->execute_role_id,
            'description' => $this->description,
            'departmentId' => $this->department_id,
            'executeRoleCode' => $this->execute_role_code,
            'executeRoleName' => $this->execute_role_name,
            'departmentCode' => $this->department_code,
            'departmentName' => $this->department_name,
        ];
    }
}
