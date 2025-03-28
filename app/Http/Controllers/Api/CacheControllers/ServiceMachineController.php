<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceMachineDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceMachine\CreateServiceMachineRequest;
use App\Models\HIS\ServiceMachine;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceMachineService;
use Illuminate\Http\Request;


class ServiceMachineController extends BaseApiCacheController
{
    protected $serviceMachineService;
    protected $serviceMachineDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceMachineService $serviceMachineService, ServiceMachine $serviceMachine)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceMachineService = $serviceMachineService;
        $this->serviceMachine = $serviceMachine;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->serviceMachine);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceMachineDTO = new ServiceMachineDTO(
            $this->serviceMachineName,
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
            $this->serviceId,
            $this->machineId,
            $this->param,
        );
        $this->serviceMachineService->withParams($this->serviceMachineDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceMachineName);
            } else {
                $data = $this->serviceMachineService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceMachineName);
            } else {
                $data = $this->serviceMachineService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceMachine, $this->serviceMachineName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceMachineName, $id);
        } else {
            $data = $this->serviceMachineService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceMachineRequest $request)
    {
        return $this->serviceMachineService->createServiceMachine($request);
    }
}
