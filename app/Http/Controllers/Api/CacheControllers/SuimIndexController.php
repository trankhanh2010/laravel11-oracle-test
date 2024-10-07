<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\SuimIndexDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SuimIndex\CreateSuimIndexRequest;
use App\Http\Requests\SuimIndex\UpdateSuimIndexRequest;
use App\Models\HIS\SuimIndex;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SuimIndexService;
use Illuminate\Http\Request;


class SuimIndexController extends BaseApiCacheController
{
    protected $suimIndexService;
    protected $suimIndexDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SuimIndexService $suimIndexService, SuimIndex $suimIndex)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->suimIndexService = $suimIndexService;
        $this->suimIndex = $suimIndex;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->suimIndex);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->suimIndexDTO = new SuimIndexDTO(
            $this->suimIndexName,
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
        );
        $this->suimIndexService->withParams($this->suimIndexDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->suimIndexName);
            } else {
                $data = $this->suimIndexService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->suimIndexName);
            } else {
                $data = $this->suimIndexService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->suimIndex, $this->suimIndexName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->suimIndexName, $id);
        } else {
            $data = $this->suimIndexService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateSuimIndexRequest $request)
    {
        return $this->suimIndexService->createSuimIndex($request);
    }
    public function update(UpdateSuimIndexRequest $request, $id)
    {
        return $this->suimIndexService->updateSuimIndex($id, $request);
    }
    public function destroy($id)
    {
        return $this->suimIndexService->deleteSuimIndex($id);
    }
}
