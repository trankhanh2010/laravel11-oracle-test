<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RelationDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\RelationList\CreateRelationListRequest;
use App\Http\Requests\RelationList\UpdateRelationListRequest;
use App\Models\EMR\Relation;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\RelationService;
use Illuminate\Http\Request;


class RelationController extends BaseApiCacheController
{
    protected $relationService;
    protected $relationDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, RelationService $relationService, Relation $relation)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->relationService = $relationService;
        $this->relation = $relation;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->relation);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->relationDTO = new RelationDTO(
            $this->relationName,
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
        $this->relationService->withParams($this->relationDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->relationName);
            } else {
                $data = $this->relationService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->relationName);
            } else {
                $data = $this->relationService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->relation, $this->relationName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->relationName, $id);
        } else {
            $data = $this->relationService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateRelationListRequest $request)
    {
        return $this->relationService->createRelation($request);
    }
    public function update(UpdateRelationListRequest $request, $id)
    {
        return $this->relationService->updateRelation($id, $request);
    }
    public function destroy($id)
    {
        return $this->relationService->deleteRelation($id);
    }
}
