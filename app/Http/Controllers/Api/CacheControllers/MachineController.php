<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MachineDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Machine\CreateMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use App\Models\HIS\Machine;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MachineService;
use Illuminate\Http\Request;


class MachineController extends BaseApiCacheController
{
    protected $machineService;
    protected $machineDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MachineService $machineService, Machine $machine)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->machineService = $machineService;
        $this->machine = $machine;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_code',
                'department_name'
            ];
            $columns = $this->getColumnsTable($this->machine);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->machineDTO = new MachineDTO(
            $this->machineName,
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
        $this->machineService->withParams($this->machineDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->machineName);
            } else {
                $data = $this->machineService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->machineName);
            } else {
                $data = $this->machineService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->machine, $this->machineName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->machineName, $id);
        } else {
            $data = $this->machineService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMachineRequest $request)
    {
        return $this->machineService->createMachine($request);
    }
    public function update(UpdateMachineRequest $request, $id)
    {
        return $this->machineService->updateMachine($id, $request);
    }
    public function destroy($id)
    {
        return $this->machineService->deleteMachine($id);
    }
}
