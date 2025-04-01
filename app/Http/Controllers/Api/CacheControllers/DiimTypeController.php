<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DiimTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DiimType\CreateDiimTypeRequest;
use App\Http\Requests\DiimType\UpdateDiimTypeRequest;
use App\Models\HIS\DiimType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DiimTypeService;
use Illuminate\Http\Request;


class DiimTypeController extends BaseApiCacheController
{
    protected $diimTypeService;
    protected $diimTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DiimTypeService $diimTypeService, DiimType $diimType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->diimTypeService = $diimTypeService;
        $this->diimType = $diimType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->diimType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->diimTypeDTO = new DiimTypeDTO(
            $this->diimTypeName,
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
        $this->diimTypeService->withParams($this->diimTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->diimTypeName);
            } else {
                $data = $this->diimTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->diimTypeName);
            } else {
                $data = $this->diimTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->diimType, $this->diimTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->diimTypeName, $id);
        } else {
            $data = $this->diimTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDiimTypeRequest $request)
    {
        return $this->diimTypeService->createDiimType($request);
    }
    public function update(UpdateDiimTypeRequest $request, $id)
    {
        return $this->diimTypeService->updateDiimType($id, $request);
    }
    public function destroy($id)
    {
        return $this->diimTypeService->deleteDiimType($id);
    }
}
