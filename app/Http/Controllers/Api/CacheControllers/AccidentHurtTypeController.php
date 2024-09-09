<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccidentHurtType\CreateAccidentHurtTypeRequest;
use App\Http\Requests\AccidentHurtType\UpdateAccidentHurtTypeRequest;
use App\Models\HIS\AccidentHurtType;
use Illuminate\Http\Request;

use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccidentHurtTypeService;

class AccidentHurtTypeController extends BaseApiCacheController
{
    protected $accidentHurtTypeService;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AccidentHurtTypeService $accidentHurtTypeService, AccidentHurtType $accidentHurtType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->accidentHurtTypeService = $accidentHurtTypeService;
        $this->accidentHurtType = $accidentHurtType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->accidentHurtType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
            $this->orderByString = arrayToCustomString($this->orderBy);
        }
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->accidentHurtTypeName);
            } else {
                $data = $this->accidentHurtTypeService->handleDataBaseSearch($keyword, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->accidentHurtTypeName);
            } else {
                $data = $this->accidentHurtTypeService->handleDataBaseGetAll($this->accidentHurtTypeName, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
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
            $validationError = $this->validateAndCheckId($id, $this->accidentHurtType, $this->accidentHurtTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->accidentHurtTypeName, $id);
        } else {
            $data = $this->accidentHurtTypeService->handleDataBaseGetWithId($this->accidentHurtTypeName, $id, $this->isActive);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAccidentHurtTypeRequest $request)
    {
        return $this->accidentHurtTypeService->createAccidentHurtType($request, $this->time, $this->appCreator, $this->appModifier);
    }
    public function update(UpdateAccidentHurtTypeRequest $request, $id)
    {
        return $this->accidentHurtTypeService->updateAccidentHurtType($this->accidentHurtTypeName, $id, $request, $this->time, $this->appModifier);
    }
    public function destroy($id)
    {
        return $this->accidentHurtTypeService->deleteAccidentHurtType($this->accidentHurtTypeName, $id);
    }
}
