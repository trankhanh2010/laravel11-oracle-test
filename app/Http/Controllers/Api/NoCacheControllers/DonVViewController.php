<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\DonVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\Treatment;
use App\Models\View\DonVView;
use App\Services\Model\DonVViewService;
use Illuminate\Http\Request;


class DonVViewController extends BaseApiCacheController
{
    protected $donVViewService;
    protected $donVViewDTO;
    protected $treatment;
    protected $serviceReq;
    public function __construct(
        Request $request,
        DonVViewService $donVViewService,
        DonVView $donVView,
        Treatment $treatment,
        ServiceReq $serviceReq,
    ) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->donVViewService = $donVViewService;
        $this->donVView = $donVView;
        $this->treatment = $treatment;
        $this->serviceReq = $serviceReq;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->donVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->donVViewDTO = new DonVViewDTO(
            $this->donVViewName,
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
            $this->tab,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->patientId,
            $this->groupBy,
            $this->intructionDate,
            $this->sessionCodes,
            $this->serviceReqId,
        );
        $this->donVViewService->withParams($this->donVViewDTO);
    }
    public function index()
    {
        if (in_array($this->tab, ['donCuKeDonThuocPhongKham', 'thuocDaKeTrongNgay'])) {
            if (!$this->patientId) {
                $this->errors[$this->patientIdName] = "Thiếu Id bệnh nhân!";
            }
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        switch ($this->tab) {
            case 'donCuKeDonThuocPhongKham': // lấy danh sách
                $data = $this->donVViewService->handleDataBaseGetAllDonCuKeDonThuocPhongKham();
                break;
            case 'thuocDaKeTrongNgay':
                $data = $this->donVViewService->handleDataBaseGetAllThuocDaKeTrongNgay();
                break;
            case 'suDungDonCu': // trong kê đơn 
                $data = $this->donVViewService->handleDataBaseGetAllSuDungDonCu();
                break;
            case 'suaDon': // trong danh sách y lệnh => sửa
                //check Treatment có đang khóa không (is_pause phải khác 1)
                if ($this->serviceReqId) {
                    $dataServiceReq = $this->serviceReq->find($this->serviceReqId);
                    $dataTreatment = $this->treatment->find($dataServiceReq->treatment_id ?? 0);

                    if ($dataTreatment) {
                        if ($dataTreatment->is_pause) {
                            $this->errors[$this->treatmentIdName] = "Hồ sơ điều trị đang bị khóa!";
                        }
                    } else {
                        $this->errors[$this->treatmentIdName] = "Không tìm thấy hồ sơ điều trị!";
                    }
                } else {
                    $this->errors[$this->serviceReqIdName] = "Thiếu mã y lệnh!";
                }
                if ($this->checkParam()) {
                    return $this->checkParam();
                }

                $data = $this->donVViewService->handleDataBaseGetAllSuaDon();

                // check xem đơn có phải là đơn không lấy không (exp_mest.is_not_taken phải khác 1)
                foreach ($data['data'] as $key => $item) {
                    if ($item->is_not_taken) {
                        $this->errors['donKhongLay'] = "Đơn không lấy không thể sửa!";
                    }
                }
                if ($this->checkParam()) {
                    return $this->checkParam();
                }
                break;
            default:
                $data = $this->donVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->donVView, $this->donVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->donVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
