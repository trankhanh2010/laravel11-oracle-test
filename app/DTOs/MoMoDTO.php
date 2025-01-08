<?php

namespace App\DTOs;

class MoMoDTO
{
    public $appCreator;
    public $appModifier;
    public $request;
    public function __construct(
        $appCreator,
        $appModifier,
        $request,
    )
    {
        $this->appCreator = $appCreator;
        $this->appModifier = $appModifier;
        $this->request = $request;
    }
}