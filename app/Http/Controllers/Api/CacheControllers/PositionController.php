<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PositionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Position\CreatePositionRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Models\HIS\Position;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PositionService;
use Illuminate\Http\Request;


class PositionController extends BaseApiCacheController
{
    protected $positionService;
    protected $positionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PositionService $positionService, Position $position)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->positionService = $positionService;
        $this->position = $position;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->position);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->positionDTO = new PositionDTO(
            $this->positionName,
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
        );
        $this->positionService->withParams($this->positionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->positionName);
            } else {
                $data = $this->positionService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->positionName);
            } else {
                $data = $this->positionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->position, $this->positionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->positionName, $id);
        } else {
            $data = $this->positionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePositionRequest $request)
    {
        return $this->positionService->createPosition($request);
    }
    public function update(UpdatePositionRequest $request, $id)
    {
        return $this->positionService->updatePosition($id, $request);
    }
    public function destroy($id)
    {
        return $this->positionService->deletePosition($id);
    }
}
