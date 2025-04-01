<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PtttConditionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttCondition\CreatePtttConditionRequest;
use App\Http\Requests\PtttCondition\UpdatePtttConditionRequest;
use App\Models\HIS\PtttCondition;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PtttConditionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class PtttConditionController extends BaseApiCacheController
{
    protected $ptttConditionService;
    protected $ptttConditionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PtttConditionService $ptttConditionService, PtttCondition $ptttCondition)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ptttConditionService = $ptttConditionService;
        $this->ptttCondition = $ptttCondition;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->ptttCondition);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ptttConditionDTO = new PtttConditionDTO(
            $this->ptttConditionName,
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
            $this->param,
            $this->noCache,
        );
        $this->ptttConditionService->withParams($this->ptttConditionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'pttt_condition_code',
            'pttt_condition_name',
        ];
        $this->elasticCustom = $this->ptttConditionService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if (!$keyword) {
                $cacheKey = $this->ptttConditionName .'_'. 'elastic' . '_' . $this->param;
                $cacheKeySet = "cache_keys:" . $this->ptttConditionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttConditionName, $this->elasticCustom, $source);
                    return $data;
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

            } else {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttConditionName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->ptttConditionService->handleDataBaseSearch();
            } else {
                $data = $this->ptttConditionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ptttCondition, $this->ptttConditionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ptttConditionName, $id);
        } else {
            $data = $this->ptttConditionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePtttConditionRequest $request)
    {
        return $this->ptttConditionService->createPtttCondition($request);
    }
    public function update(UpdatePtttConditionRequest $request, $id)
    {
        return $this->ptttConditionService->updatePtttCondition($id, $request);
    }
    public function destroy($id)
    {
        return $this->ptttConditionService->deletePtttCondition($id);
    }
}
