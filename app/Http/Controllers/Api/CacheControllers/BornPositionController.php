<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BornPositionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BornPosition\CreateBornPositionRequest;
use App\Http\Requests\BornPosition\UpdateBornPositionRequest;
use App\Models\HIS\BornPosition;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BornPositionService;
use Illuminate\Http\Request;


class BornPositionController extends BaseApiCacheController
{
    protected $bornPositionService;
    protected $bornPositionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BornPositionService $bornPositionService, BornPosition $bornPosition)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bornPositionService = $bornPositionService;
        $this->bornPosition = $bornPosition;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bornPosition);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bornPositionDTO = new BornPositionDTO(
            $this->bornPositionName,
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
        $this->bornPositionService->withParams($this->bornPositionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bornPositionName);
            } else {
                $data = $this->bornPositionService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bornPositionName);
            } else {
                $data = $this->bornPositionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bornPosition, $this->bornPositionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bornPositionName, $id);
        } else {
            $data = $this->bornPositionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBornPositionRequest $request)
    {
        return $this->bornPositionService->createBornPosition($request);
    }
    public function update(UpdateBornPositionRequest $request, $id)
    {
        return $this->bornPositionService->updateBornPosition($id, $request);
    }
    public function destroy($id)
    {
        return $this->bornPositionService->deleteBornPosition($id);
    }
}
