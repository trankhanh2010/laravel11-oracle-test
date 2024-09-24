<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PtttTableDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttTable\CreatePtttTableRequest;
use App\Http\Requests\PtttTable\UpdatePtttTableRequest;
use App\Models\HIS\PtttTable;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PtttTableService;
use Illuminate\Http\Request;


class PtttTableController extends BaseApiCacheController
{
    protected $ptttTableService;
    protected $ptttTableDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PtttTableService $ptttTableService, PtttTable $ptttTable)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ptttTableService = $ptttTableService;
        $this->ptttTable = $ptttTable;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'execute_room_code',
                'execute_room_name',
                'max_request_by_day',
                'department_code',
                'department_name',
                'area_code',
                'area_name',
            ];
            $columns = $this->getColumnsTable($this->ptttTable);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ptttTableDTO = new PtttTableDTO(
            $this->ptttTableName,
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
        $this->ptttTableService->withParams($this->ptttTableDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttTableName);
            } else {
                $data = $this->ptttTableService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->ptttTableName);
            } else {
                $data = $this->ptttTableService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ptttTable, $this->ptttTableName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ptttTableName, $id);
        } else {
            $data = $this->ptttTableService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePtttTableRequest $request)
    {
        return $this->ptttTableService->createPtttTable($request);
    }
    public function update(UpdatePtttTableRequest $request, $id)
    {
        return $this->ptttTableService->updatePtttTable($id, $request);
    }
    public function destroy($id)
    {
        return $this->ptttTableService->deletePtttTable($id);
    }
}
