<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServTeinVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServTeinVView\CreateSereServTeinVViewRequest;
use App\Http\Requests\SereServTeinVView\UpdateSereServTeinVViewRequest;
use App\Models\View\SereServTeinVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServTeinVViewService;
use Illuminate\Http\Request;


class SereServTeinVViewController extends BaseApiCacheController
{
    protected $sereServTeinVViewService;
    protected $sereServTeinVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServTeinVViewService $sereServTeinVViewService, SereServTeinVView $sereServTeinVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServTeinVViewService = $sereServTeinVViewService;
        $this->sereServTeinVView = $sereServTeinVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServTeinVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServTeinVViewDTO = new SereServTeinVViewDTO(
            $this->sereServTeinVViewName,
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
            $this->sereServIds,
        );
        $this->sereServTeinVViewService->withParams($this->sereServTeinVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->sereServTeinVViewName);
            } else {
                $data = $this->sereServTeinVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->sereServTeinVViewName);
            } else {
                $data = $this->sereServTeinVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServTeinVView, $this->sereServTeinVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServTeinVViewName, $id);
        } else {
            $data = $this->sereServTeinVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
