<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MaterialTypeMapDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MaterialTypeMap\CreateMaterialTypeMapRequest;
use App\Http\Requests\MaterialTypeMap\UpdateMaterialTypeMapRequest;
use App\Models\HIS\MaterialTypeMap;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MaterialTypeMapService;
use Illuminate\Http\Request;


class MaterialTypeMapController extends BaseApiCacheController
{
    protected $materialTypeMapService;
    protected $materialTypeMapDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MaterialTypeMapService $materialTypeMapService, MaterialTypeMap $materialTypeMap)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->materialTypeMapService = $materialTypeMapService;
        $this->materialTypeMap = $materialTypeMap;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->materialTypeMap);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->materialTypeMapDTO = new MaterialTypeMapDTO(
            $this->materialTypeMapName,
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
        $this->materialTypeMapService->withParams($this->materialTypeMapDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->materialTypeMapName);
            } else {
                $data = $this->materialTypeMapService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->materialTypeMapName);
            } else {
                $data = $this->materialTypeMapService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->materialTypeMap, $this->materialTypeMapName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->materialTypeMapName, $id);
        } else {
            $data = $this->materialTypeMapService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMaterialTypeMapRequest $request)
    {
        return $this->materialTypeMapService->createMaterialTypeMap($request);
    }
    public function update(UpdateMaterialTypeMapRequest $request, $id)
    {
        return $this->materialTypeMapService->updateMaterialTypeMap($id, $request);
    }
    public function destroy($id)
    {
        return $this->materialTypeMapService->deleteMaterialTypeMap($id);
    }
}
