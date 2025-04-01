<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServTeinDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServTein\CreateSereServTeinRequest;
use App\Http\Requests\SereServTein\UpdateSereServTeinRequest;
use App\Models\HIS\SereServTein;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServTeinService;
use Illuminate\Http\Request;


class SereServTeinController extends BaseApiCacheController
{
    protected $sereServTeinService;
    protected $sereServTeinDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServTeinService $sereServTeinService, SereServTein $sereServTein)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServTeinService = $sereServTeinService;
        $this->sereServTein = $sereServTein;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServTein);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServTeinDTO = new SereServTeinDTO(
            $this->sereServTeinName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->testIndexIds,
            $this->tdlTreatmentId,
            $this->param,
            $this->noCache,
        );
        $this->sereServTeinService->withParams($this->sereServTeinDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->sereServTeinName);
            } else {
                $data = $this->sereServTeinService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->sereServTeinName);
            } else {
                $data = $this->sereServTeinService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServTein, $this->sereServTeinName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServTeinName, $id);
        } else {
            $data = $this->sereServTeinService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
