<?php

namespace App\Traits;

trait dinh_dang_ten_truong
{
    /// Xóa gạch _ và chuyển về chữ thường tất cả tên trường
    public function toLower($key)
    {
        return strtolower($key);
    }

    public function snakeToCamel($key)
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }

    // public function toArray()
    // {
    //     $array = parent::toArray();
    //     $convertedArray = [];
    //     foreach ($array as $key => $value) {
    //         $convertedKey = $this->toLower($this->snakeToCamel($key));
    //         $convertedArray[$convertedKey] = $value;
    //     }
    //     return $convertedArray;
    // }
    public function toArray()
    {
        $array = parent::toArray();
        $convertedArray = [];
        foreach ($array as $key => $value) {
            $convertedKey = $this->snakeToCamel($key);
            // Ép kiểu riêng cho những trường muốn là số
            if (in_array($convertedKey, ['key',])) {
                $convertedArray[$convertedKey] = (((int) $value) > 0) ?((int) $value) : $value; // Nếu có thể chuyển sang số thì chuyển , không thì giữ nguyên
            } else {
                $convertedArray[$convertedKey] = $value;
            }
        }
        return $convertedArray;
    }
}
