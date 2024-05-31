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
            $convertedArray[$convertedKey] = $value;
        }
        return $convertedArray;
    }
}
