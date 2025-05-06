<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\UserAccountBookVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\UserAccountBookVView\CreateUserAccountBookVViewRequest;
use App\Http\Requests\UserAccountBookVView\UpdateUserAccountBookVViewRequest;
use App\Models\View\UserAccountBookVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\UserAccountBookVViewService;
use Illuminate\Http\Request;


class UserAccountBookVViewController extends BaseApiCacheController
{
    protected $userAccountBookVViewService;
    protected $userAccountBookVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, UserAccountBookVViewService $userAccountBookVViewService, UserAccountBookVView $userAccountBookVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->userAccountBookVViewService = $userAccountBookVViewService;
        $this->userAccountBookVView = $userAccountBookVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->userAccountBookVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->userAccountBookVViewDTO = new UserAccountBookVViewDTO(
            $this->userAccountBookVViewName,
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
            $this->isForDeposit,
            $this->isForRepay,
            $this->isForBill,
            $this->param,
            $this->noCache,
            $this->tab,
            $this->currentLoginname,
        );
        $this->userAccountBookVViewService->withParams($this->userAccountBookVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword == null) {
            $data = $this->userAccountBookVViewService->handleDataBaseGetAll();
        } else {
            $data = $this->userAccountBookVViewService->handleDataBaseSearch();
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
            $validationError = $this->validateAndCheckId($id, $this->userAccountBookVView, $this->userAccountBookVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->userAccountBookVViewName, $id);
        } else {
            $data = $this->userAccountBookVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
