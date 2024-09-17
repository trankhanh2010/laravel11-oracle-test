<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\FuexTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\FuexType\CreateFuexTypeRequest;
use App\Http\Requests\FuexType\UpdateFuexTypeRequest;
use App\Models\HIS\FuexType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\FuexTypeService;
use Illuminate\Http\Request;


class FuexTypeController extends BaseApiCacheController
{
    protected $fuexTypeService;
    protected $fuexTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, FuexTypeService $fuexTypeService, FuexType $fuexType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->fuexTypeService = $fuexTypeService;
        $this->fuexType = $fuexType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->fuexType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->fuexTypeDTO = new FuexTypeDTO(
            $this->fuexTypeName,
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
        $this->fuexTypeService->withParams($this->fuexTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->fuexTypeName);
            } else {
                $data = $this->fuexTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->fuexTypeName);
            } else {
                $data = $this->fuexTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->fuexType, $this->fuexTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->fuexTypeName, $id);
        } else {
            $data = $this->fuexTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
