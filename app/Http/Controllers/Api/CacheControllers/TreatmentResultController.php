<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TreatmentResultDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentResult\CreateTreatmentResultRequest;
use App\Http\Requests\TreatmentResult\UpdateTreatmentResultRequest;
use App\Models\HIS\TreatmentResult;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TreatmentResultController extends BaseApiCacheController
{
    protected $treatmentResultService;
    protected $treatmentResultDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentResultService $treatmentResultService, TreatmentResult $treatmentResult)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentResultService = $treatmentResultService;
        $this->treatmentResult = $treatmentResult;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentResult);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentResultDTO = new TreatmentResultDTO(
            $this->treatmentResultName,
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
            $this->tab,
        );
        $this->treatmentResultService->withParams($this->treatmentResultDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'treatment_result_code',
            'treatment_result_name',
        ];
        $this->elasticCustom = $this->treatmentResultService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if (!$keyword) {
                $data = Cache::remember($this->treatmentResultName . '_' . $this->param, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentResultName, $this->elasticCustom, $source);
                    return $data;
                });

            } else {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentResultName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->treatmentResultService->handleDataBaseSearch();
            } else {
                $data = $this->treatmentResultService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentResult, $this->treatmentResultName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentResultName, $id);
        } else {
            $data = $this->treatmentResultService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
