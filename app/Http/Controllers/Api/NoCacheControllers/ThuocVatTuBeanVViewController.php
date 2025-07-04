<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\ThuocVatTuBeanVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ThuocVatTuBeanVView\CreateThuocVatTuBeanVViewRequest;
use App\Http\Requests\ThuocVatTuBeanVView\UpdateThuocVatTuBeanVViewRequest;
use App\Models\View\ThuocVatTuBeanVView;
use App\Services\Model\ThuocVatTuBeanVViewService;
use Illuminate\Http\Request;


class ThuocVatTuBeanVViewController extends BaseApiCacheController
{
    protected $thuocVatTuBeanVViewService;
    protected $thuocVatTuBeanVViewDTO;
    public function __construct(Request $request, ThuocVatTuBeanVViewService $thuocVatTuBeanVViewService, ThuocVatTuBeanVView $thuocVatTuBeanVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->thuocVatTuBeanVViewService = $thuocVatTuBeanVViewService;
        $this->thuocVatTuBeanVView = $thuocVatTuBeanVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->thuocVatTuBeanVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->thuocVatTuBeanVViewDTO = new ThuocVatTuBeanVViewDTO(
            $this->thuocVatTuBeanVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->param,
            $this->noCache,
            $this->mediStockIds,
            $this->tab,
            $this->type,
            $this->intructionTime,
        );
        $this->thuocVatTuBeanVViewService->withParams($this->thuocVatTuBeanVViewDTO);
    }
    public function index()
    {
        if ($this->tab == 'keDonThuocPhongKham') {
            if (!$this->mediStockIds && $this->type == 'thuocVatTuTrongKho') {
                $this->errors[$this->mediStockIdsName] = "Chưa chọn kho xuất!";
            }
            if (!$this->intructionTime) {
                $this->errors[$this->intructionTimeName] = "Thiếu thời gian chỉ định!";
            }
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            $data = $this->thuocVatTuBeanVViewService->handleDataBaseSearch();
        } else {
            $data = $this->thuocVatTuBeanVViewService->handleDataBaseGetAll();
        }
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
