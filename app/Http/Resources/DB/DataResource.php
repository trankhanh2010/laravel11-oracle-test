<?php

namespace App\Http\Resources\DB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Lấy tất cả dữ liệu từ resource gốc
        $originalData = parent::toArray($request);

        // Chuyển đổi toàn bộ key của mảng
        return $this->transformKeysToCamelCase($originalData);
    }

    /**
     * Đệ quy chuyển đổi tất cả các key trong mảng từ snake_case sang camelCase.
     *
     * @param  array  $data
     * @return array
     */
    private function transformKeysToCamelCase(array $data)
    {
        $transformed = [];

        foreach ($data as $key => $value) {
            // Chuyển key sang camelCase
            $camelCaseKey = $this->snakeToCamel($key);

            // Nếu value là một mảng, đệ quy xử lý tiếp
            if (is_array($value)) {
                $transformed[$camelCaseKey] = $this->transformKeysToCamelCase($value);
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
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}
