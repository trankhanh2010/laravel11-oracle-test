<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceGroup\CreateServiceGroupRequest;
use App\Http\Requests\ServiceGroup\UpdateServiceGroupRequest;
use App\Models\HIS\ServiceGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceGroupService;
use Illuminate\Http\Request;


class ServiceGroupController extends BaseApiCacheController
{
    protected $serviceGroupService;
    protected $serviceGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceGroupService $serviceGroupService, ServiceGroup $serviceGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceGroupService = $serviceGroupService;
        $this->serviceGroup = $serviceGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->serviceGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceGroupDTO = new ServiceGroupDTO(
            $this->serviceGroupName,
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
        $this->serviceGroupService->withParams($this->serviceGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceGroupName);
            } else {
                $data = $this->serviceGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceGroupName);
            } else {
                $data = $this->serviceGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceGroup, $this->serviceGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceGroupName, $id);
        } else {
            $data = $this->serviceGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceGroupRequest $request)
    {
        return $this->serviceGroupService->createServiceGroup($request);
    }
    public function update(UpdateServiceGroupRequest $request, $id)
    {
        return $this->serviceGroupService->updateServiceGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->serviceGroupService->deleteServiceGroup($id);
    }
}
