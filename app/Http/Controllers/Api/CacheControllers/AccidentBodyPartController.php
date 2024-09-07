<?php

namespace App\Http\Controllers\Api\CacheControllers;


use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccidentBodyPart\CreateAccidentBodyPartRequest;
use App\Http\Requests\AccidentBodyPart\UpdateAccidentBodyPartRequest;
use App\Models\HIS\AccidentBodyPart;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccidentBodyPartService;
use Illuminate\Http\Request;


class AccidentBodyPartController extends BaseApiCacheController
{
    protected $accidentBodyPartService;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AccidentBodyPartService $accidentBodyPartService, AccidentBodyPart $accidentBodyPart)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->accidentBodyPartService = $accidentBodyPartService;
        $this->accidentBodyPart = $accidentBodyPart;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->accidentBodyPart);
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
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->accidentBodyPartName);
            } else {
                $data = $this->accidentBodyPartService->handleDataBaseSearch($keyword, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->accidentBodyPartName);
            } else {
                $data = $this->accidentBodyPartService->handleDataBaseGetAll($this->accidentBodyPartName, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
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
            $validationError = $this->validateAndCheckId($id, $this->accidentBodyPart, $this->accidentBodyPartName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->accidentBodyPartName, $id);
        } else {
            $data = $this->accidentBodyPartService->handleDataBaseGetWithId($this->accidentBodyPartName, $id, $this->isActive);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAccidentBodyPartRequest $request)
    {
        return $this->accidentBodyPartService->createAccidentBodyPart($request, $this->time, $this->appCreator, $this->appModifier);
    }
    public function update(UpdateAccidentBodyPartRequest $request, $id)
    {
        return $this->accidentBodyPartService->updateAccidentBodyPart($this->accidentBodyPartName, $id, $request, $this->time, $this->appModifier);
    }
    public function destroy($id)
    {
        return $this->accidentBodyPartService->deleteAccidentBodyPart($this->accidentBodyPartName, $id);
    }
}
