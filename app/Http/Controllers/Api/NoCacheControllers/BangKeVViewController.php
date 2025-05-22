<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\BangKeVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BangKe\UpdateBangKeRequest;
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
            $this->orderByJoin = [];
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
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->amountGreaterThan0,
            $this->tab,
            $this->status,
        );
        $this->bangKeVViewService->withParams($this->bangKeVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($this->keyword) {
            $data = $this->bangKeVViewService->handleDataBaseSearch();
        } else {
            $data = $this->bangKeVViewService->handleDataBaseGetAll();
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
    public function handleBieuMau()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        switch ($this->tab) {
            case 'bangKeNgoaiTruHaoPhi':
                $data = $this->bangKeVViewService->bangKeNgoaiTruHaoPhi();
                break;
            case 'bangKeNgoaiTruBHYTHaoPhi':
                $data = $this->bangKeVViewService->bangKeNgoaiTruBHYTHaoPhi();
                break;
            case 'bangKeNgoaiTruVienPhiTPTB':
                $data = $this->bangKeVViewService->bangKeNgoaiTruVienPhiTPTB();
                break;
            case 'bangKeNgoaiTruBHYTTheoKhoa6556QDBYT':
                $data = $this->bangKeVViewService->bangKeNgoaiTruBHYTTheoKhoa6556QDBYT();
                break;
            case 'bangKeNgoaiTruVienPhiTheoKhoa':
                $data = $this->bangKeVViewService->bangKeNgoaiTruVienPhiTheoKhoa();
                break;
            case 'bangKeNoiTruHaoPhi':
                $data = $this->bangKeVViewService->bangKeNoiTruHaoPhi();
                break;
            case 'bangKeNoiTruBHYTTheoKhoa6556QDBYT':
                $data = $this->bangKeVViewService->bangKeNoiTruBHYTTheoKhoa6556QDBYT();
                break;
            case 'bangKeTongHop6556KhoaPhongThanhToan':
                $data = $this->bangKeVViewService->bangKeTongHop6556KhoaPhongThanhToan();
                break;
            case 'tongHopNgoaiTruVienPhiHaoPhi':
                $data = $this->bangKeVViewService->tongHopNgoaiTruVienPhiHaoPhi();
                break;
            default:
                return returnDataSuccess([], []);
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

    public function update(UpdateBangKeRequest $request, $id)
    {
        return $this->bangKeVViewService->updateBangKe($id, $request);
    }
}
