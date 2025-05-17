<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\BangKeVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\BangKeVView;
use App\Services\Model\BangKeVViewService;
use Illuminate\Http\Request;


class BangKeVViewController extends BaseApiCacheController
{
    protected $bangKeVViewService;
    protected $bangKeVViewDTO;
    public function __construct(Request $request, BangKeVViewService $bangKeVViewService, BangKeVView $bangKeVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->bangKeVViewService = $bangKeVViewService;
        $this->bangKeVView = $bangKeVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bangKeVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bangKeVViewDTO = new BangKeVViewDTO(
            $this->bangKeVViewName,
            $this->keyword,
            $this->isActive,
            $this->orderBy,
            $this->orderByJoin,
            $this->orderByString,
            $this->getAll,
            $this->start,
            $this->limit,
            $request,
            $this->appCreator, 
            $this->appModifier, 
            $this->time,
            $this->treatmentId,
            $this->param,
            $this->noCache,
            $this->groupBy,
        );
        $this->bangKeVViewService->withParams($this->bangKeVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->bangKeVViewService->handleDataBaseGetAll();
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->keywordName => $this->keyword,
            $this->orderByName => $this->orderByRequest
        ];
        return returnDataSuccess($paramReturn, $data['data']);
    }

}
