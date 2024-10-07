<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\SuimIndexUnitDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SuimIndexUnit\CreateSuimIndexUnitRequest;
use App\Http\Requests\SuimIndexUnit\UpdateSuimIndexUnitRequest;
use App\Models\HIS\SuimIndexUnit;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SuimIndexUnitService;
use Illuminate\Http\Request;


class SuimIndexUnitController extends BaseApiCacheController
{
    protected $suimIndexUnitService;
    protected $suimIndexUnitDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SuimIndexUnitService $suimIndexUnitService, SuimIndexUnit $suimIndexUnit)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->suimIndexUnitService = $suimIndexUnitService;
        $this->suimIndexUnit = $suimIndexUnit;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->suimIndexUnit);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->suimIndexUnitDTO = new SuimIndexUnitDTO(
            $this->suimIndexUnitName,
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
        $this->suimIndexUnitService->withParams($this->suimIndexUnitDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->suimIndexUnitName);
            } else {
                $data = $this->suimIndexUnitService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->suimIndexUnitName);
            } else {
                $data = $this->suimIndexUnitService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->suimIndexUnit, $this->suimIndexUnitName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->suimIndexUnitName, $id);
        } else {
            $data = $this->suimIndexUnitService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateSuimIndexUnitRequest $request)
    {
        return $this->suimIndexUnitService->createSuimIndexUnit($request);
    }
    public function update(UpdateSuimIndexUnitRequest $request, $id)
    {
        return $this->suimIndexUnitService->updateSuimIndexUnit($id, $request);
    }
    public function destroy($id)
    {
        return $this->suimIndexUnitService->deleteSuimIndexUnit($id);
    }
}
