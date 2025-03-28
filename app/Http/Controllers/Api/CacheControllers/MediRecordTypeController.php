<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MediRecordTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediRecordType\CreateMediRecordTypeRequest;
use App\Http\Requests\MediRecordType\UpdateMediRecordTypeRequest;
use App\Models\HIS\MediRecordType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MediRecordTypeService;
use Illuminate\Http\Request;


class MediRecordTypeController extends BaseApiCacheController
{
    protected $mediRecordTypeService;
    protected $mediRecordTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MediRecordTypeService $mediRecordTypeService, MediRecordType $mediRecordType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mediRecordTypeService = $mediRecordTypeService;
        $this->mediRecordType = $mediRecordType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->mediRecordType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mediRecordTypeDTO = new MediRecordTypeDTO(
            $this->mediRecordTypeName,
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
        $this->mediRecordTypeService->withParams($this->mediRecordTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mediRecordTypeName);
            } else {
                $data = $this->mediRecordTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mediRecordTypeName);
            } else {
                $data = $this->mediRecordTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mediRecordType, $this->mediRecordTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mediRecordTypeName, $id);
        } else {
            $data = $this->mediRecordTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMediRecordTypeRequest $request)
    {
        return $this->mediRecordTypeService->createMediRecordType($request);
    }
    public function update(UpdateMediRecordTypeRequest $request, $id)
    {
        return $this->mediRecordTypeService->updateMediRecordType($id, $request);
    }
    public function destroy($id)
    {
        return $this->mediRecordTypeService->deleteMediRecordType($id);
    }
}
