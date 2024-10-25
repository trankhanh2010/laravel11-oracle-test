<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\AccountBookVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccountBookVView\CreateAccountBookVViewRequest;
use App\Http\Requests\AccountBookVView\UpdateAccountBookVViewRequest;
use App\Models\View\AccountBookVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccountBookVViewService;
use Illuminate\Http\Request;


class AccountBookVViewController extends BaseApiCacheController
{
    protected $accountBookVViewService;
    protected $accountBookVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AccountBookVViewService $accountBookVViewService, AccountBookVView $accountBookVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->accountBookVViewService = $accountBookVViewService;
        $this->accountBookVView = $accountBookVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->accountBookVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->accountBookVViewDTO = new AccountBookVViewDTO(
            $this->accountBookVViewName,
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
        );
        $this->accountBookVViewService->withParams($this->accountBookVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->accountBookVViewName);
            } else {
                $data = $this->accountBookVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->accountBookVViewName);
            } else {
                $data = $this->accountBookVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->accountBookVView, $this->accountBookVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->accountBookVViewName, $id);
        } else {
            $data = $this->accountBookVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
