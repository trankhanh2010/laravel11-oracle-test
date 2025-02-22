<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentRoomGroupVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentRoomGroupVView\CreateTreatmentRoomGroupVViewRequest;
use App\Http\Requests\TreatmentRoomGroupVView\UpdateTreatmentRoomGroupVViewRequest;
use App\Models\View\TreatmentRoomGroupVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentRoomGroupVViewService;
use Illuminate\Http\Request;


class TreatmentRoomGroupVViewController extends BaseApiCacheController
{
    protected $treatmentRoomGroupVViewService;
    protected $treatmentRoomGroupVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentRoomGroupVViewService $treatmentRoomGroupVViewService, TreatmentRoomGroupVView $treatmentRoomGroupVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentRoomGroupVViewService = $treatmentRoomGroupVViewService;
        $this->treatmentRoomGroupVView = $treatmentRoomGroupVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentRoomGroupVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentRoomGroupVViewDTO = new TreatmentRoomGroupVViewDTO(
            $this->treatmentRoomGroupVViewName,
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
            $this->departmentCode,
        );
        $this->treatmentRoomGroupVViewService->withParams($this->treatmentRoomGroupVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            $data = $this->treatmentRoomGroupVViewService->handleDataBaseSearch();
        } else {
            $data = $this->treatmentRoomGroupVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentRoomGroupVView, $this->treatmentRoomGroupVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->treatmentRoomGroupVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
