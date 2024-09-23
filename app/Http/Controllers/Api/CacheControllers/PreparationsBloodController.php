<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PreparationsBloodDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PreparationsBlood\CreatePreparationsBloodRequest;
use App\Http\Requests\PreparationsBlood\UpdatePreparationsBloodRequest;
use App\Models\HIS\PreparationsBlood;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PreparationsBloodService;
use Illuminate\Http\Request;


class PreparationsBloodController extends BaseApiCacheController
{
    protected $preparationsBloodService;
    protected $preparationsBloodDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PreparationsBloodService $preparationsBloodService, PreparationsBlood $preparationsBlood)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->preparationsBloodService = $preparationsBloodService;
        $this->preparationsBlood = $preparationsBlood;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->preparationsBlood);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->preparationsBloodDTO = new PreparationsBloodDTO(
            $this->preparationsBloodName,
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
        $this->preparationsBloodService->withParams($this->preparationsBloodDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->preparationsBloodName);
            } else {
                $data = $this->preparationsBloodService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->preparationsBloodName);
            } else {
                $data = $this->preparationsBloodService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->preparationsBlood, $this->preparationsBloodName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->preparationsBloodName, $id);
        } else {
            $data = $this->preparationsBloodService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePreparationsBloodRequest $request)
    {
        return $this->preparationsBloodService->createPreparationsBlood($request);
    }
    public function update(UpdatePreparationsBloodRequest $request, $id)
    {
        return $this->preparationsBloodService->updatePreparationsBlood($id, $request);
    }
    public function destroy($id)
    {
        return $this->preparationsBloodService->deletePreparationsBlood($id);
    }
}
