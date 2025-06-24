<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\ServiceReqListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\ServiceReqListVView;
use App\Services\Model\ServiceReqListVViewService;
use Illuminate\Http\Request;


class ServiceReqListVViewController extends BaseApiCacheController
{
    protected $serviceReqListVViewService;
    protected $serviceReqListVViewDTO;
    public function __construct(Request $request, ServiceReqListVViewService $serviceReqListVViewService, ServiceReqListVView $serviceReqListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->serviceReqListVViewService = $serviceReqListVViewService;
        $this->serviceReqListVView = $serviceReqListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->serviceReqListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceReqListVViewDTO = new ServiceReqListVViewDTO(
            $this->serviceReqListVViewName,
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
            $this->groupBy,
            $this->trackingId,
            $this->treatmentId,
            $this->param,
            $this->noCache,
            $this->treatmentCode,
            $this->tab,
            $this->patientId,
            $this->serviceReqIds,
            $this->patientCode,
            $this->serviceReqCode,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->executeRoomId,
            $this->serviceReqTypeIds,
            $this->serviceReqSttIds,
        );
        $this->serviceReqListVViewService->withParams($this->serviceReqListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }

        switch ($this->tab) {
            case 'chiDinhCuChiDinhDichVuKyThuat':
                if ($this->patientId == null) {
                    $this->errors[$this->patientIdName] = "Thiếu Id bệnh nhân!";
                }
                if ($this->checkParam()) {
                    return $this->checkParam();
                }
                $data = $this->serviceReqListVViewService->handleDataBaseGetAllChiDinhCuChiDinhDichVuKyThuat();
                break;

            case 'chiTietDon':
                if ($this->serviceReqIds == null) {
                    $this->errors[$this->serviceReqIdsName] = "Thiếu danh sách Id y lệnh!";
                }
                if ($this->checkParam()) {
                    return $this->checkParam();
                }
                $data = $this->serviceReqListVViewService->handleDataBaseGetAllWithChiTietDon();
                break;
            case 'chiDinh':
                if ($this->patientCode == null) {
                    $this->errors[$this->patientCodeName] = "Thiếu mã bệnh nhân!";
                }
                if ($this->checkParam()) {
                    return $this->checkParam();
                }
                $data = $this->serviceReqListVViewService->handleDataBaseGetAllChiDinh();
                break;

            default:
                $keyword = $this->keyword;
                if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
                    $data = $this->serviceReqListVViewService->handleDataBaseSearch();
                } else {
                    $data = $this->serviceReqListVViewService->handleDataBaseGetAll();
                }
                break;
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

    public function show($id)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($id !== null) {
            $validationError = $this->validateAndCheckId($id, $this->serviceReqListVView, $this->serviceReqListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->serviceReqListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
