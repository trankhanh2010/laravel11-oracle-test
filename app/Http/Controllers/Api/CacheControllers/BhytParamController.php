<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BhytParamDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BhytParam\CreateBhytParamRequest;
use App\Http\Requests\BhytParam\UpdateBhytParamRequest;
use App\Models\HIS\BhytParam;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BhytParamService;
use Illuminate\Http\Request;


class BhytParamController extends BaseApiCacheController
{
    protected $bhytParamService;
    protected $bhytParamDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BhytParamService $bhytParamService, BhytParam $bhytParam)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bhytParamService = $bhytParamService;
        $this->bhytParam = $bhytParam;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bhytParam);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bhytParamDTO = new BhytParamDTO(
            $this->bhytParamName,
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
        $this->bhytParamService->withParams($this->bhytParamDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bhytParamName);
            } else {
                $data = $this->bhytParamService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bhytParamName);
            } else {
                $data = $this->bhytParamService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bhytParam, $this->bhytParamName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bhytParamName, $id);
        } else {
            $data = $this->bhytParamService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBhytParamRequest $request)
    {
        return $this->bhytParamService->createBhytParam($request);
    }
    public function update(UpdateBhytParamRequest $request, $id)
    {
        return $this->bhytParamService->updateBhytParam($id, $request);
    }
    public function destroy($id)
    {
        return $this->bhytParamService->deleteBhytParam($id);
    }
}
