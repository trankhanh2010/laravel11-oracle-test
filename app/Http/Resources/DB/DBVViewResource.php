<?php

namespace App\Http\Resources\DB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DBVViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Chuyển stdClass thành mảng
        $data = (array) $this->resource;

        // Chuyển đổi snake_case sang camelCase
        $converted = [];
        foreach ($data as $key => $value) {
            $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $converted[$camelCaseKey] = $value;
        }

        return $converted;
    }
}
