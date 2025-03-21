<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentExecuteRoomListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\TreatmentExecuteRoomListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentExecuteRoomListVViewService;
use Illuminate\Http\Request;


class TreatmentExecuteRoomListVViewController extends BaseApiCacheController
{
    protected $treatmentExecuteRoomListVViewService;
    protected $treatmentExecuteRoomListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentExecuteRoomListVViewService $treatmentExecuteRoomListVViewService, TreatmentExecuteRoomListVView $treatmentExecuteRoomListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentExecuteRoomListVViewService = $treatmentExecuteRoomListVViewService;
        $this->treatmentExecuteRoomListVView = $treatmentExecuteRoomListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentExecuteRoomListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentExecuteRoomListVViewDTO = new TreatmentExecuteRoomListVViewDTO(
            $this->treatmentExecuteRoomListVViewName,
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
            $this->treatmentTypeIds,
            $this->isCoTreatDepartment,
            $this->patientClassifyIds,
            $this->isOut,
            $this->addLoginname,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->groupBy,
            $this->executeRoomCode,
            $this->executeRoomIds,
            $this->serviceReqSttCodes,
        );
        $this->treatmentExecuteRoomListVViewService->withParams($this->treatmentExecuteRoomListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }

        $data = $this->treatmentExecuteRoomListVViewService->handleDataBaseSearch();
           
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentExecuteRoomListVView, $this->treatmentExecuteRoomListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }

        $data = $this->treatmentExecuteRoomListVViewService->handleDataBaseGetWithId($id);

        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
