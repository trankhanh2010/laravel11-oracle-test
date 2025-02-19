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
        );
        $this->deathCauseService->withParams($this->deathCauseDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->deathCauseName);
            } else {
                $data = $this->deathCauseService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->deathCauseName);
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
