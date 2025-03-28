<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentBedRoomLViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentBedRoomLView\CreateTreatmentBedRoomLViewRequest;
use App\Http\Requests\TreatmentBedRoomLView\UpdateTreatmentBedRoomLViewRequest;
use App\Models\View\TreatmentBedRoomLView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentBedRoomLViewService;
use Illuminate\Http\Request;


class TreatmentBedRoomLViewController extends BaseApiCacheController
{
    protected $treatmentBedRoomLViewService;
    protected $treatmentBedRoomLViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentBedRoomLViewService $treatmentBedRoomLViewService, TreatmentBedRoomLView $treatmentBedRoomLView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentBedRoomLViewService = $treatmentBedRoomLViewService;
        $this->treatmentBedRoomLView = $treatmentBedRoomLView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentBedRoomLView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentBedRoomLViewDTO = new TreatmentBedRoomLViewDTO(
            $this->treatmentBedRoomLViewName,
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
            $this->bedRoomIds,
            $this->addTimeTo,
            $this->addTimeFrom,
            $this->isInRoom,
            $this->param,
        );
        $this->treatmentBedRoomLViewService->withParams($this->treatmentBedRoomLViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentBedRoomLViewName);
            } else {
                $data = $this->treatmentBedRoomLViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->treatmentBedRoomLViewName);
            } else {
                $data = $this->treatmentBedRoomLViewService->handleDataBaseGetAll();
            }
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentBedRoomLView, $this->treatmentBedRoomLViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentBedRoomLViewName, $id);
        } else {
            $data = $this->treatmentBedRoomLViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
