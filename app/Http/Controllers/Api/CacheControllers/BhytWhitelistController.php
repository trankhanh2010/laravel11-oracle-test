<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BhytWhitelistDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BhytWhitelist\CreateBhytWhitelistRequest;
use App\Http\Requests\BhytWhitelist\UpdateBhytWhitelistRequest;
use App\Models\HIS\BhytWhitelist;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BhytWhitelistService;
use Illuminate\Http\Request;


class BhytWhitelistController extends BaseApiCacheController
{
    protected $bhytWhitelistService;
    protected $bhytWhitelistDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BhytWhitelistService $bhytWhitelistService, BhytWhitelist $bhytWhitelist)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bhytWhitelistService = $bhytWhitelistService;
        $this->bhytWhitelist = $bhytWhitelist;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bhytWhitelist);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bhytWhitelistDTO = new BhytWhitelistDTO(
            $this->bhytWhitelistName,
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
        $this->bhytWhitelistService->withParams($this->bhytWhitelistDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bhytWhitelistName);
            } else {
                $data = $this->bhytWhitelistService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bhytWhitelistName);
            } else {
                $data = $this->bhytWhitelistService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bhytWhitelist, $this->bhytWhitelistName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bhytWhitelistName, $id);
        } else {
            $data = $this->bhytWhitelistService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBhytWhitelistRequest $request)
    {
        return $this->bhytWhitelistService->createBhytWhitelist($request);
    }
    public function update(UpdateBhytWhitelistRequest $request, $id)
    {
        return $this->bhytWhitelistService->updateBhytWhitelist($id, $request);
    }
    public function destroy($id)
    {
        return $this->bhytWhitelistService->deleteBhytWhitelist($id);
    }
}
