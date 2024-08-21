<?php

namespace App\Http\Resources\Elastic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElasticResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            '_id' => $this->resource['_id'] ?? null,
            '_index' => $this->resource['_index'] ?? null,
            '_score' => $this->resource['_score'] ?? null,
            '_source' => $this->convertKeysToCamelCase($this->resource['_source'] ?? []),
            'highlight' => $this->convertKeysToCamelCase($this->resource['highlight'] ?? []),
            'sort' => $this->resource['sort'] ?? null,
        ];
    }

    // Hàm để chuyển đổi tên trường từ snake_case sang camelCase
    private function convertKeysToCamelCase(array $data)
    {
        $converted = [];
        foreach ($data as $key => $value) {
            $newKey = $this->snakeToCamel($key);
            $converted[$newKey] = is_array($value) ? $this->convertKeysToCamelCase($value) : $value;
        }
        return $converted;
    }

    // Hàm chuyển đổi snake_case thành camelCase
    private function snakeToCamel($string)
    {
        $result = str_replace('_', '', ucwords($string, '_'));
        return lcfirst($result);
    }
}
