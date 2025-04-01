<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\MedicalCaseCoverListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicalCaseCoverListVView\CreateMedicalCaseCoverListVViewRequest;
use App\Http\Requests\MedicalCaseCoverListVView\UpdateMedicalCaseCoverListVViewRequest;
use App\Models\View\MedicalCaseCoverListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicalCaseCoverListVViewService;
use Illuminate\Http\Request;


class MedicalCaseCoverListVViewController extends BaseApiCacheController
{
    protected $medicalCaseCoverListVViewService;
    protected $medicalCaseCoverListVViewDTO;
    public function __construct(Request $request, MedicalCaseCoverListVViewService $medicalCaseCoverListVViewService, MedicalCaseCoverListVView $medicalCaseCoverListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medicalCaseCoverListVViewService = $medicalCaseCoverListVViewService;
        $this->medicalCaseCoverListVView = $medicalCaseCoverListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicalCaseCoverListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicalCaseCoverListVViewDTO = new MedicalCaseCoverListVViewDTO(
            $this->medicalCaseCoverListVViewName,
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
            $this->param,
            $this->noCache,
        );
        $this->medicalCaseCoverListVViewService->withParams($this->medicalCaseCoverListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }

        $data = $this->medicalCaseCoverListVViewService->handleDataBaseSearch();
           
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
            $validationError = $this->validateAndCheckId($id, $this->medicalCaseCoverListVView, $this->medicalCaseCoverListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }

        $data = $this->medicalCaseCoverListVViewService->handleDataBaseGetWithId($id);

        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
