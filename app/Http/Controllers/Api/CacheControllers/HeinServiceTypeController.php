<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\HeinServiceTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\HeinServiceType\CreateHeinServiceTypeRequest;
use App\Http\Requests\HeinServiceType\UpdateHeinServiceTypeRequest;
use App\Models\HIS\HeinServiceType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\HeinServiceTypeService;
use Illuminate\Http\Request;


class HeinServiceTypeController extends BaseApiCacheController
{
    protected $heinServiceTypeService;
    protected $heinServiceTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, HeinServiceTypeService $heinServiceTypeService, HeinServiceType $heinServiceType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->heinServiceTypeService = $heinServiceTypeService;
        $this->heinServiceType = $heinServiceType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->heinServiceType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->heinServiceTypeDTO = new HeinServiceTypeDTO(
            $this->heinServiceTypeName,
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
        $this->heinServiceTypeService->withParams($this->heinServiceTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->heinServiceTypeName);
            } else {
                $data = $this->heinServiceTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->heinServiceTypeName);
            } else {
                $data = $this->heinServiceTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->heinServiceType, $this->heinServiceTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->heinServiceTypeName, $id);
        } else {
            $data = $this->heinServiceTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
