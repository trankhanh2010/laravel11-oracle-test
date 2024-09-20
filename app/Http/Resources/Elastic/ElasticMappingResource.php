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
        $transformed = $this->transformToCamelCase($mapping);

        return ['properties' => $transformed];
    }

    private function transformToCamelCase(array $mapping)
    {
        $transformed = [];

        foreach ($mapping as $key => $value) {
            $camelCaseKey = $this->snakeToCamel($key);
            
            // If the value is an array, recursively convert keys inside it
            if (is_array($value)) {
                $transformed[$camelCaseKey] = $this->transformToCamelCase($value);
            } else {
                $transformed[$camelCaseKey] = $value;
            }
        }

        return $transformed;
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
