<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PatientTypeRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PatientTypeRoom\CreatePatientTypeRoomRequest;
use App\Http\Requests\PatientTypeRoom\UpdatePatientTypeRoomRequest;
use App\Models\HIS\PatientTypeRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PatientTypeRoomService;
use Illuminate\Http\Request;


class PatientTypeRoomController extends BaseApiCacheController
{
    protected $patientTypeRoomService;
    protected $patientTypeRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PatientTypeRoomService $patientTypeRoomService, PatientTypeRoom $patientTypeRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->patientTypeRoomService = $patientTypeRoomService;
        $this->patientTypeRoom = $patientTypeRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->patientTypeRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientTypeRoomDTO = new PatientTypeRoomDTO(
            $this->patientTypeRoomName,
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
            $this->patientTypeId,
            $this->roomId,
            $this->param,
        );
        $this->patientTypeRoomService->withParams($this->patientTypeRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->patientTypeRoomName);
            } else {
                $data = $this->patientTypeRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->patientTypeRoomName);
            } else {
                $data = $this->patientTypeRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->patientTypeRoom, $this->patientTypeRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->patientTypeRoomName, $id);
        } else {
            $data = $this->patientTypeRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePatientTypeRoomRequest $request)
    {
        return $this->patientTypeRoomService->createPatientTypeRoom($request);
    }
}
