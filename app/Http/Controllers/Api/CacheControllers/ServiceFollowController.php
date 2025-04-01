<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceFollowDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceFollow\CreateServiceFollowRequest;
use App\Http\Requests\ServiceFollow\UpdateServiceFollowRequest;
use App\Models\HIS\ServiceFollow;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceFollowService;
use Illuminate\Http\Request;


class ServiceFollowController extends BaseApiCacheController
{
    protected $serviceFollowService;
    protected $serviceFollowDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceFollowService $serviceFollowService, ServiceFollow $serviceFollow)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceFollowService = $serviceFollowService;
        $this->serviceFollow = $serviceFollow;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'serviceName',
                'serviceCode',
                'serviceTypeName',
                'serviceTypeCode',
                'serviceFollowName',
                'serviceFollowCode',
                'serviceFollowTypeName',
                'serviceFollowTypeCode',
            ];
            $columns = $this->getColumnsTable($this->serviceFollow);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceFollowDTO = new ServiceFollowDTO(
            $this->serviceFollowName,
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
        $this->serviceFollowService->withParams($this->serviceFollowDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceFollowName);
            } else {
                $data = $this->serviceFollowService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceFollowName);
            } else {
                $data = $this->serviceFollowService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceFollow, $this->serviceFollowName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceFollowName, $id);
        } else {
            $data = $this->serviceFollowService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceFollowRequest $request)
    {
        return $this->serviceFollowService->createServiceFollow($request);
    }
    public function update(UpdateServiceFollowRequest $request, $id)
    {
        return $this->serviceFollowService->updateServiceFollow($id, $request);
    }
    public function destroy($id)
    {
        return $this->serviceFollowService->deleteServiceFollow($id);
    }
}
