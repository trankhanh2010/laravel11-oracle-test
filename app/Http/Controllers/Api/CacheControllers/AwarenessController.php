<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AwarenessDTO;
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
    protected $awarenessDTO;
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
        }
        // Thêm tham số vào service
        $this->awarenessDTO = new AwarenessDTO(
            $this->awarenessName,
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
        $this->awarenessService->withParams($this->awarenessDTO);
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
                $data = $this->awarenessService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->awarenessName);
            } else {
                $data = $this->awarenessService->handleDataBaseGetAll();
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
            $data = $this->awarenessService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAwarenessRequest $request)
    {
        return $this->awarenessService->createAwareness($request);
    }
    public function update(UpdateAwarenessRequest $request, $id)
    {
        return $this->awarenessService->updateAwareness($id, $request);
    }
    public function destroy($id)
    {
        return $this->awarenessService->deleteAwareness($id);
    }
}

