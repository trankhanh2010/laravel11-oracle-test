<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentBedRoomListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentBedRoomListVView\CreateTreatmentBedRoomListVViewRequest;
use App\Http\Requests\TreatmentBedRoomListVView\UpdateTreatmentBedRoomListVViewRequest;
use App\Models\View\TreatmentBedRoomListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentBedRoomListVViewService;
use Illuminate\Http\Request;


class TreatmentBedRoomListVViewController extends BaseApiCacheController
{
    protected $treatmentBedRoomListVViewService;
    protected $treatmentBedRoomListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentBedRoomListVViewService $treatmentBedRoomListVViewService, TreatmentBedRoomListVView $treatmentBedRoomListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentBedRoomListVViewService = $treatmentBedRoomListVViewService;
        $this->treatmentBedRoomListVView = $treatmentBedRoomListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentBedRoomListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentBedRoomListVViewDTO = new TreatmentBedRoomListVViewDTO(
            $this->treatmentBedRoomListVViewName,
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
            $this->isInBed,
            $this->bedRoomIds,
            $this->treatmentTypeIds,
            $this->isCoTreatDepartment,
            $this->patientClassifyIds,
            $this->isOut,
            $this->addLoginname,
            $this->addTimeFrom,
            $this->addTimeTo,
            $this->groupBy,
            $this->executeRoomCode,
            $this->executeRoomIds,
        );
        $this->treatmentBedRoomListVViewService->withParams($this->treatmentBedRoomListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }

        $data = $this->treatmentBedRoomListVViewService->handleDataBaseSearch();
           
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentBedRoomListVView, $this->treatmentBedRoomListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }

        $data = $this->treatmentBedRoomListVViewService->handleDataBaseGetWithId($id);

        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
