<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PackingTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PackingType\CreatePackingTypeRequest;
use App\Http\Requests\PackingType\UpdatePackingTypeRequest;
use App\Models\HIS\PackingType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PackingTypeService;
use Illuminate\Http\Request;


class PackingTypeController extends BaseApiCacheController
{
    protected $packingTypeService;
    protected $packingTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PackingTypeService $packingTypeService, PackingType $packingType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->packingTypeService = $packingTypeService;
        $this->packingType = $packingType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->packingType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->packingTypeDTO = new PackingTypeDTO(
            $this->packingTypeName,
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
        $this->packingTypeService->withParams($this->packingTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->packingTypeName);
            } else {
                $data = $this->packingTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->packingTypeName);
            } else {
                $data = $this->packingTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->packingType, $this->packingTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->packingTypeName, $id);
        } else {
            $data = $this->packingTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePackingTypeRequest $request)
    {
        return $this->packingTypeService->createPackingType($request);
    }
    public function update(UpdatePackingTypeRequest $request, $id)
    {
        return $this->packingTypeService->updatePackingType($id, $request);
    }
    public function destroy($id)
    {
        return $this->packingTypeService->deletePackingType($id);
    }
}
