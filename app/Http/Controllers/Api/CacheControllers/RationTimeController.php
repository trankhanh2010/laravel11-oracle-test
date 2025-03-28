<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RationTimeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\RationTime\CreateRationTimeRequest;
use App\Http\Requests\RationTime\UpdateRationTimeRequest;
use App\Models\HIS\RationTime;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\RationTimeService;
use Illuminate\Http\Request;


class RationTimeController extends BaseApiCacheController
{
    protected $rationTimeService;
    protected $rationTimeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, RationTimeService $rationTimeService, RationTime $rationTime)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->rationTimeService = $rationTimeService;
        $this->rationTime = $rationTime;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->rationTime);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->rationTimeDTO = new RationTimeDTO(
            $this->rationTimeName,
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
        $this->rationTimeService->withParams($this->rationTimeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->rationTimeName);
            } else {
                $data = $this->rationTimeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->rationTimeName);
            } else {
                $data = $this->rationTimeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->rationTime, $this->rationTimeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->rationTimeName, $id);
        } else {
            $data = $this->rationTimeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateRationTimeRequest $request)
    {
        return $this->rationTimeService->createRationTime($request);
    }
    public function update(UpdateRationTimeRequest $request, $id)
    {
        return $this->rationTimeService->updateRationTime($id, $request);
    }
    public function destroy($id)
    {
        return $this->rationTimeService->deleteRationTime($id);
    }
}
