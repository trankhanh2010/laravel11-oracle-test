<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Awareness\CreateAwarenessRequest;
use App\Http\Requests\Awareness\UpdateAwarenessRequest;
use App\Models\HIS\Awareness;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AwarenessService;
use Illuminate\Http\Request;


class AwarenessController extends BaseApiCacheController
{
    protected $awarenessService;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AwarenessService $awarenessService, Awareness $awareness)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->awarenessService = $awarenessService;
        $this->awareness = $awareness;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->awareness);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
            $this->orderByString = arrayToCustomString($this->orderBy);
        }
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->awarenessName);
            } else {
                $data = $this->awarenessService->handleDataBaseSearch($keyword, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->awarenessName);
            } else {
                $data = $this->awarenessService->handleDataBaseGetAll($this->awarenessName, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
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
            $validationError = $this->validateAndCheckId($id, $this->awareness, $this->awarenessName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->awarenessName, $id);
        } else {
            $data = $this->awarenessService->handleDataBaseGetWithId($this->awarenessName, $id, $this->isActive);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAwarenessRequest $request)
    {
        return $this->awarenessService->createAwareness($request, $this->time, $this->appCreator, $this->appModifier);
    }
    public function update(UpdateAwarenessRequest $request, $id)
    {
        return $this->awarenessService->updateAwareness($this->awarenessName, $id, $request, $this->time, $this->appModifier);
    }
    public function destroy($id)
    {
        return $this->awarenessService->deleteAwareness($this->awarenessName, $id);
    }
}

