<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MilitaryRankDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MilitaryRank\CreateMilitaryRankRequest;
use App\Http\Requests\MilitaryRank\UpdateMilitaryRankRequest;
use App\Models\HIS\MilitaryRank;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MilitaryRankService;
use Illuminate\Http\Request;


class MilitaryRankController extends BaseApiCacheController
{
    protected $militaryRankService;
    protected $militaryRankDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MilitaryRankService $militaryRankService, MilitaryRank $militaryRank)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->militaryRankService = $militaryRankService;
        $this->militaryRank = $militaryRank;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->militaryRank);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->militaryRankDTO = new MilitaryRankDTO(
            $this->militaryRankName,
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
        $this->militaryRankService->withParams($this->militaryRankDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->militaryRankName);
            } else {
                $data = $this->militaryRankService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->militaryRankName);
            } else {
                $data = $this->militaryRankService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->militaryRank, $this->militaryRankName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->militaryRankName, $id);
        } else {
            $data = $this->militaryRankService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }

    public function store(CreateMilitaryRankRequest $request)
    {
        return $this->militaryRankService->createMilitaryRank($request);
    }
    public function update(UpdateMilitaryRankRequest $request, $id)
    {
        return $this->militaryRankService->updateMilitaryRank($id, $request);
    }
    public function destroy($id)
    {
        return $this->militaryRankService->deleteMilitaryRank($id);
    }
}
