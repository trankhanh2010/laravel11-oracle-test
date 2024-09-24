<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RefectoryDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Refectory\CreateRefectoryRequest;
use App\Http\Requests\Refectory\UpdateRefectoryRequest;
use App\Models\HIS\Refectory;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\RefectoryService;
use Illuminate\Http\Request;


class RefectoryController extends BaseApiCacheController
{
    protected $refectoryService;
    protected $refectoryDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, RefectoryService $refectoryService, Refectory $refectory)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->refectoryService = $refectoryService;
        $this->refectory = $refectory;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_id',
                'department_name',
                'department_code',
            ];
            $columns = $this->getColumnsTable($this->refectory);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->refectoryDTO = new RefectoryDTO(
            $this->refectoryName,
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
        $this->refectoryService->withParams($this->refectoryDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->refectoryName);
            } else {
                $data = $this->refectoryService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->refectoryName);
            } else {
                $data = $this->refectoryService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->refectory, $this->refectoryName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->refectoryName, $id);
        } else {
            $data = $this->refectoryService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateRefectoryRequest $request)
    {
        return $this->refectoryService->createRefectory($request);
    }
    public function update(UpdateRefectoryRequest $request, $id)
    {
        return $this->refectoryService->updateRefectory($id, $request);
    }
    public function destroy($id)
    {
        return $this->refectoryService->deleteRefectory($id);
    }
}
