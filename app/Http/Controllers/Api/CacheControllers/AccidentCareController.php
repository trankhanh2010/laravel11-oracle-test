<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AccidentCareDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AccidentCare\CreateAccidentCareRequest;
use App\Http\Requests\AccidentCare\UpdateAccidentCareRequest;
use App\Models\HIS\AccidentCare;
use Illuminate\Http\Request;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AccidentCareService;


class AccidentCareController extends BaseApiCacheController
{
    protected $accidentCareService;
    protected $accidentCareDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AccidentCareService $accidentCareService, AccidentCare $accidentCare)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->accidentCareService = $accidentCareService;
        $this->accidentCare = $accidentCare;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->accidentCare);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->accidentCareDTO = new AccidentCareDTO(
            $this->accidentCareName,
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
        $this->accidentCareService->withParams($this->accidentCareDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->accidentCareName);
            } else {
                $data = $this->accidentCareService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->accidentCareName);
            } else {
                $data = $this->accidentCareService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->accidentCare, $this->accidentCareName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->accidentCareName, $id);
        } else {
            $data = $this->accidentCareService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAccidentCareRequest $request)
    {
        return $this->accidentCareService->createAccidentCare($request);
    }
    public function update(UpdateAccidentCareRequest $request, $id)
    {
        return $this->accidentCareService->updateAccidentCare($id, $request);
    }
    public function destroy($id)
    {
        return $this->accidentCareService->deleteAccidentCare($id);
    }
}
