<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\NextTreaIntrDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\NextTreaIntr\CreateNextTreaIntrRequest;
use App\Http\Requests\NextTreaIntr\UpdateNextTreaIntrRequest;
use App\Models\HIS\NextTreaIntr;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\NextTreaIntrService;
use Illuminate\Http\Request;


class NextTreaIntrController extends BaseApiCacheController
{
    protected $nextTreaIntrService;
    protected $nextTreaIntrDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, NextTreaIntrService $nextTreaIntrService, NextTreaIntr $nextTreaIntr)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->nextTreaIntrService = $nextTreaIntrService;
        $this->nextTreaIntr = $nextTreaIntr;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->nextTreaIntr);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->nextTreaIntrDTO = new NextTreaIntrDTO(
            $this->nextTreaIntrName,
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
            $this->param,
            $this->noCache,
        );
        $this->nextTreaIntrService->withParams($this->nextTreaIntrDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->nextTreaIntrName);
            } else {
                $data = $this->nextTreaIntrService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->nextTreaIntrName);
            } else {
                $data = $this->nextTreaIntrService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->nextTreaIntr, $this->nextTreaIntrName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->nextTreaIntrName, $id);
        } else {
            $data = $this->nextTreaIntrService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
