<?php

namespace App\Http\Resources\Elastic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElasticMappingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $mapping = $this->resource['mappings']['properties'] ?? [];
        $transformed = [];

        foreach ($mapping as $key => $value) {
            $camelCaseKey = $this->snakeToCamel($key);
            $transformed[$camelCaseKey] = $value;
        }

        return ['properties' => $transformed];
    }

    /**
     * Convert a snake_case string to camelCase.
     *
     * @param  string  $string
     * @return string
     */
    private function snakeToCamel($string)
    {
        // Chuyển snake_case thành camelCase
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}
