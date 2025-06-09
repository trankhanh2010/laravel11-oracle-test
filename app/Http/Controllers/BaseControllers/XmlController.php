<?php

namespace App\Http\Controllers\BaseControllers;

use App\Services\Xml\XmlService;
use Illuminate\Http\Request;

class XmlController extends BaseApiCacheController
{
    protected $xmlService;
    public function __construct(
        Request $request,
        XmlService $xmlService,
    ) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->xmlService = $xmlService;
    }
    public function insertDataFromXml130ToDb(){
        $this->xmlService->insertDataFromXml130ToDB();
    }
}
