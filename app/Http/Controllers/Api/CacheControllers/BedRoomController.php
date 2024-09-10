<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BedRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BedRoom\CreateBedRoomRequest;
use App\Http\Requests\BedRoom\UpdateBedRoomRequest;
use App\Models\HIS\BedRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BedRoomService;
use Illuminate\Http\Request;


class BedRoomController extends BaseApiCacheController
{
    protected $bedRoomService;
    protected $bedRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BedRoomService $bedRoomService, BedRoom $bedRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bedRoomService = $bedRoomService;
        $this->bedRoom = $bedRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'is_pause',
                'department_id',
                'department_name',
                'department_code',
                'area_name',
                'area_code',
                'speciality_name',
                'speciality_code',
                'cashier_room_name',
                'cashier_room_code',
                'patient_type_name',
                'patient_type_code',
            ];
            $columns = $this->getColumnsTable($this->bedRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bedRoomDTO = new BedRoomDTO(
            $this->bedRoomName,
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
            $this->departmentId,
        );
        $this->bedRoomService->withParams($this->bedRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bedRoomName);
            } else {
                $data = $this->bedRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bedRoomName);
            } else {
                $data = $this->bedRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bedRoom, $this->bedRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bedRoomName, $id);
        } else {
            $data = $this->bedRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBedRoomRequest $request)
    {
        return $this->bedRoomService->createBedRoom($request);
    }
    public function update(UpdateBedRoomRequest $request, $id)
    {
        return $this->bedRoomService->updateBedRoom($id, $request);
    }
    public function destroy($id)
    {
        return $this->bedRoomService->deleteBedRoom($id);
    }
}
