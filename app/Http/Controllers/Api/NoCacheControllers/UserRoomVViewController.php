<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\UserRoomVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\UserRoomVView\CreateUserRoomVViewRequest;
use App\Http\Requests\UserRoomVView\UpdateUserRoomVViewRequest;
use App\Models\View\UserRoomVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\UserRoomVViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserRoomVViewController extends BaseApiCacheController
{
    protected $userRoomVViewService;
    protected $userRoomVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, UserRoomVViewService $userRoomVViewService, UserRoomVView $userRoomVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->userRoomVViewService = $userRoomVViewService;
        $this->userRoomVView = $userRoomVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->userRoomVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->userRoomVViewDTO = new UserRoomVViewDTO(
            $this->userRoomVViewName,
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
            $this->tab,
            $this->currentLoginname,
        );
        $this->userRoomVViewService->withParams($this->userRoomVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $this->elasticCustom = $this->userRoomVViewService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $data = Cache::remember($this->userRoomVViewName.'_'.$this->currentLoginname.'_' . $this->param, $this->time, function () {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->userRoomVViewName, $this->elasticCustom);
                    return $data;
                });
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->userRoomVViewName, $this->elasticCustom);
            }
        } else {
            if ($keyword) {
                $data = $this->userRoomVViewService->handleDataBaseSearch();
            } else {
                $data = $this->userRoomVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->userRoomVView, $this->userRoomVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->userRoomVViewName, $id);
        } else {
            $data = $this->userRoomVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
