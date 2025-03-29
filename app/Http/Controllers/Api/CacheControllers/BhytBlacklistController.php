<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BhytBlacklistDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BhytBlacklist\CreateBhytBlacklistRequest;
use App\Http\Requests\BhytBlacklist\UpdateBhytBlacklistRequest;
use App\Models\HIS\BHYTBlacklist;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BhytBlacklistService;
use Illuminate\Http\Request;


class BhytBlacklistController extends BaseApiCacheController
{
    protected $bhytBlacklistService;
    protected $bhytBlacklistDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BhytBlacklistService $bhytBlacklistService, BhytBlacklist $bhytBlacklist)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bhytBlacklistService = $bhytBlacklistService;
        $this->bhytBlacklist = $bhytBlacklist;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bhytBlacklist);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bhytBlacklistDTO = new BhytBlacklistDTO(
            $this->bhytBlacklistName,
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
        );
        $this->bhytBlacklistService->withParams($this->bhytBlacklistDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bhytBlacklistName);
            } else {
                $data = $this->bhytBlacklistService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bhytBlacklistName);
            } else {
                $data = $this->bhytBlacklistService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bhytBlacklist, $this->bhytBlacklistName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bhytBlacklistName, $id);
        } else {
            $data = $this->bhytBlacklistService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBhytBlacklistRequest $request)
    {
        return $this->bhytBlacklistService->createBhytBlacklist($request);
    }
    public function update(UpdateBhytBlacklistRequest $request, $id)
    {
        return $this->bhytBlacklistService->updateBhytBlacklist($id, $request);
    }
    public function destroy($id)
    {
        return $this->bhytBlacklistService->deleteBhytBlacklist($id);
    }
}
