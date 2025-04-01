<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MestRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MestRoom\CreateMestRoomRequest;
use App\Http\Requests\MestRoom\UpdateMestRoomRequest;
use App\Models\HIS\MestRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MestRoomService;
use Illuminate\Http\Request;


class MestRoomController extends BaseApiCacheController
{
    protected $mestRoomService;
    protected $mestRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MestRoomService $mestRoomService, MestRoom $mestRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mestRoomService = $mestRoomService;
        $this->mestRoom = $mestRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'medi_stock_code',
                'medi_stock_name',
                'room_code',
                'room_name',
                'room_type_code',
                'room_type_name',
                'department_code',
                'department_name'
            ];
            $columns = $this->getColumnsTable($this->mestRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mestRoomDTO = new MestRoomDTO(
            $this->mestRoomName,
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
            $this->mediStockId,
            $this->roomId,
            $this->param,
            $this->noCache,
        );
        $this->mestRoomService->withParams($this->mestRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mestRoomName);
            } else {
                $data = $this->mestRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mestRoomName);
            } else {
                $data = $this->mestRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mestRoom, $this->mestRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mestRoomName, $id);
        } else {
            $data = $this->mestRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMestRoomRequest $request)
    {
        return $this->mestRoomService->createMestRoom($request);
    }
}
