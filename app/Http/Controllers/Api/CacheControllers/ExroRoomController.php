<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExroRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExroRoom\CreateExroRoomRequest;
use App\Http\Requests\ExroRoom\UpdateExroRoomRequest;
use App\Models\HIS\ExroRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExroRoomService;
use Illuminate\Http\Request;


class ExroRoomController extends BaseApiCacheController
{
    protected $exroRoomService;
    protected $exroRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExroRoomService $exroRoomService, ExroRoom $exroRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->exroRoomService = $exroRoomService;
        $this->exroRoom = $exroRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->exroRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->exroRoomDTO = new ExroRoomDTO(
            $this->exroRoomName,
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
            $this->roomId,
            $this->executeRoomId,
            $this->param,
            $this->noCache,
        );
        $this->exroRoomService->withParams($this->exroRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->exroRoomName);
            } else {
                $data = $this->exroRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->exroRoomName);
            } else {
                $data = $this->exroRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->exroRoom, $this->exroRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->exroRoomName, $id);
        } else {
            $data = $this->exroRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExroRoomRequest $request)
    {
        return $this->exroRoomService->createExroRoom($request);
    }
}
