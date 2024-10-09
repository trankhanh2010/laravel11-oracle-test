<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\UserRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\UserRoom\CreateUserRoomRequest;
use App\Http\Requests\UserRoom\UpdateUserRoomRequest;
use App\Models\HIS\UserRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\UserRoomService;
use Illuminate\Http\Request;


class UserRoomController extends BaseApiCacheController
{
    protected $userRoomService;
    protected $userRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, UserRoomService $userRoomService, UserRoom $userRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->userRoomService = $userRoomService;
        $this->userRoom = $userRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'is_pause',
                'department_id',
                'room_type_id',
                'g_code',
                'room_type_code',
                'room_type_name',
                'branch_id',
                'department_code',
                'department_name',
                'branch_code',
                'branch_name',
                'hein_medi_org_code',
                'room_name',
                'room_code',
            ];
            $columns = $this->getColumnsTable($this->userRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->userRoomDTO = new UserRoomDTO(
            $this->userRoomName,
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
            $this->loginname,
        );
        $this->userRoomService->withParams($this->userRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->userRoomName);
            } else {
                $data = $this->userRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->userRoomName);
            } else {
                $data = $this->userRoomService->handleDataBaseGetAll();
            }
        }
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->isDeleteName => $this->isDelete,
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
            $validationError = $this->validateAndCheckId($id, $this->userRoom, $this->userRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->userRoomName, $id);
        } else {
            $data = $this->userRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
