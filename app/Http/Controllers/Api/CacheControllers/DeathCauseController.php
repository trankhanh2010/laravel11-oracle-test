<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DeathCauseDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DeathCause\CreateDeathCauseRequest;
use App\Http\Requests\DeathCause\UpdateDeathCauseRequest;
use App\Models\HIS\DeathCause;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DeathCauseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class DeathCauseController extends BaseApiCacheController
{
    protected $deathCauseService;
    protected $deathCauseDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DeathCauseService $deathCauseService, DeathCause $deathCause)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->deathCauseService = $deathCauseService;
        $this->deathCause = $deathCause;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->deathCause);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->deathCauseDTO = new DeathCauseDTO(
            $this->deathCauseName,
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
        $this->deathCauseService->withParams($this->deathCauseDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'death_cause_code',
            'death_cause_name',
        ];
        $this->elasticCustom = $this->deathCauseService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $cacheKey = $this->deathCauseName .'_'. 'elastic' . '_' . $this->param;
                $cacheKeySet = "cache_keys:" . $this->deathCauseName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->deathCauseName, $this->elasticCustom, $source);
                    return $data;
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->deathCauseName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->deathCauseService->handleDataBaseSearch();
            } else {
                $data = $this->deathCauseService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->deathCause, $this->deathCauseName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->deathCauseName, $id);
        } else {
            $data = $this->deathCauseService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
