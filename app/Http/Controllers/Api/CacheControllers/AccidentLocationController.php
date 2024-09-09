<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccidentLocation\CreateAccidentLocationRequest;
use App\Http\Requests\AccidentLocation\UpdateAccidentLocationRequest;
use App\Models\HIS\AccidentLocation;
use Illuminate\Http\Request;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccidentLocationService;

class AccidentLocationController extends BaseApiCacheController
{
    protected $accidentLocationService;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AccidentLocationService $accidentLocationService, AccidentLocation $accidentLocation)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->accidentLocationService = $accidentLocationService;
        $this->accidentLocation = $accidentLocation;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->accidentLocation);
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
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->accidentLocationName);
            } else {
                $data = $this->accidentLocationService->handleDataBaseSearch($keyword, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->accidentLocationName);
            } else {
                $data = $this->accidentLocationService->handleDataBaseGetAll($this->accidentLocationName, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
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
            $validationError = $this->validateAndCheckId($id, $this->accidentLocation, $this->accidentLocationName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->accidentLocationName, $id);
        } else {
            $data = $this->accidentLocationService->handleDataBaseGetWithId($this->accidentLocationName, $id, $this->isActive);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAccidentLocationRequest $request)
    {
        return $this->accidentLocationService->createAccidentLocation($request, $this->time, $this->appCreator, $this->appModifier);
    }
    public function update(UpdateAccidentLocationRequest $request, $id)
    {
        return $this->accidentLocationService->updateAccidentLocation($this->accidentLocationName, $id, $request, $this->time, $this->appModifier);
    }
    public function destroy($id)
    {
        return $this->accidentLocationService->deleteAccidentLocation($this->accidentLocationName, $id);
    }
}
