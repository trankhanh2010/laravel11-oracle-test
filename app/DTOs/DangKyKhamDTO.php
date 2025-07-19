<?php

namespace App\DTOs;

class DangKyKhamDTO
{
    public $request;
    public function __construct(
        $request,
        )
    {
        $this->request = $request;
    }
}