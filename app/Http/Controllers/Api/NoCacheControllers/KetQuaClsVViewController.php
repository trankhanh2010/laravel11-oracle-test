<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\KetQuaClsVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\KetQuaClsVView;
use App\Services\Model\KetQuaClsVViewService;
use Illuminate\Http\Request;


class KetQuaClsVViewController extends BaseApiCacheController
{
    protected $ketQuaClsVViewService;
    protected $ketQuaClsVViewDTO;
    public function __construct(Request $request,KetQuaClsVViewService $ketQuaClsVViewService, KetQuaClsVView $ketQuaClsVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->ketQuaClsVViewService = $ketQuaClsVViewService;
        $this->ketQuaClsVView = $ketQuaClsVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->ketQuaClsVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ketQuaClsVViewDTO = new KetQuaClsVViewDTO(
            $this->ketQuaClsVViewName,
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
            $this->treatmentId,
            $this->hienThiDichVuChaLoaiXN,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->trenNguong,
            $this->duoiNguong,
            $this->chiSoQuanTrong,
        );
        $this->ketQuaClsVViewService->withParams($this->ketQuaClsVViewDTO);
    }
    public function index()
    {
        if (
            $this->treatmentId === null
        ) {
            $this->errors[$this->treatmentIdName] = "Thiếu thông tin lần điều trị";
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;

        switch ($this->tab) {
            case 'chonKetQuaCls':
                $data = $this->ketQuaClsVViewService->handleDataBaseGetAllChonKetQuaCls();
                break;
            default:
                if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
                    $data = $this->ketQuaClsVViewService->handleDataBaseSearch();
                } else {
                    $data = $this->ketQuaClsVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ketQuaClsVView, $this->ketQuaClsVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->ketQuaClsVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
