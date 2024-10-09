<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\DebateEkipUserDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DebateEkipUser\CreateDebateEkipUserRequest;
use App\Http\Requests\DebateEkipUser\UpdateDebateEkipUserRequest;
use App\Models\HIS\DebateEkipUser;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DebateEkipUserService;
use Illuminate\Http\Request;


class DebateEkipUserController extends BaseApiCacheController
{
    protected $debateEkipUserService;
    protected $debateEkipUserDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DebateEkipUserService $debateEkipUserService, DebateEkipUser $debateEkipUser)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->debateEkipUserService = $debateEkipUserService;
        $this->debateEkipUser = $debateEkipUser;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_code',
                'department_name',
                'execute_role_code',
                'execute_role_name',
            ];
            $columns = $this->getColumnsTable($this->debateEkipUser);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->debateEkipUserDTO = new DebateEkipUserDTO(
            $this->debateEkipUserName,
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
            $this->debateId,
        );
        $this->debateEkipUserService->withParams($this->debateEkipUserDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->debateEkipUserName);
            } else {
                $data = $this->debateEkipUserService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->debateEkipUserName);
            } else {
                $data = $this->debateEkipUserService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->debateEkipUser, $this->debateEkipUserName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->debateEkipUserName, $id);
        } else {
            $data = $this->debateEkipUserService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
