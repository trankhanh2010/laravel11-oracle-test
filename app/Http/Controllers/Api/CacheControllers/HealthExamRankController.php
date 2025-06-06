<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\HealthExamRankDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\HealthExamRank\CreateHealthExamRankRequest;
use App\Http\Requests\HealthExamRank\UpdateHealthExamRankRequest;
use App\Models\HIS\HealthExamRank;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\HealthExamRankService;
use Illuminate\Http\Request;


class HealthExamRankController extends BaseApiCacheController
{
    protected $healthExamRankService;
    protected $healthExamRankDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, HealthExamRankService $healthExamRankService, HealthExamRank $healthExamRank)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->healthExamRankService = $healthExamRankService;
        $this->healthExamRank = $healthExamRank;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->healthExamRank);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->healthExamRankDTO = new HealthExamRankDTO(
            $this->healthExamRankName,
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
        $this->healthExamRankService->withParams($this->healthExamRankDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->healthExamRankName);
            } else {
                $data = $this->healthExamRankService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->healthExamRankName);
            } else {
                $data = $this->healthExamRankService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->healthExamRank, $this->healthExamRankName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->healthExamRankName, $id);
        } else {
            $data = $this->healthExamRankService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
