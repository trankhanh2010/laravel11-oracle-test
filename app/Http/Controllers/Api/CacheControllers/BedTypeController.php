<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BedTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BedType\CreateBedTypeRequest;
use App\Http\Requests\BedType\UpdateBedTypeRequest;
use App\Models\HIS\BedType;
use Illuminate\Http\Request;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BedTypeService;

class BedTypeController extends BaseApiCacheController
{
    protected $bedTypeService;
    protected $bedTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BedTypeService $bedTypeService, BedType $bedType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bedTypeService = $bedTypeService;
        $this->bedType = $bedType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bedType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bedTypeDTO = new BedTypeDTO(
            $this->bedTypeName,
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
        $this->bedTypeService->withParams($this->bedTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bedTypeName);
            } else {
                $data = $this->bedTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bedTypeName);
            } else {
                $data = $this->bedTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bedType, $this->bedTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bedTypeName, $id);
        } else {
            $data = $this->bedTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBedTypeRequest $request)
    {
        return $this->bedTypeService->createBedType($request);
    }
    public function update(UpdateBedTypeRequest $request, $id)
    {
        return $this->bedTypeService->updateBedType($id, $request);
    }
    public function destroy($id)
    {
        return $this->bedTypeService->deleteBedType($id);
    }
}
