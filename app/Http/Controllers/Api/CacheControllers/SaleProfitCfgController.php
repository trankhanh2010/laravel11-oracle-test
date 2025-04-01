<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\SaleProfitCfgDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SaleProfitCfg\CreateSaleProfitCfgRequest;
use App\Http\Requests\SaleProfitCfg\UpdateSaleProfitCfgRequest;
use App\Models\HIS\SaleProfitCfg;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SaleProfitCfgService;
use Illuminate\Http\Request;


class SaleProfitCfgController extends BaseApiCacheController
{
    protected $saleProfitCfgService;
    protected $saleProfitCfgDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SaleProfitCfgService $saleProfitCfgService, SaleProfitCfg $saleProfitCfg)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->saleProfitCfgService = $saleProfitCfgService;
        $this->saleProfitCfg = $saleProfitCfg;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->saleProfitCfg);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->saleProfitCfgDTO = new SaleProfitCfgDTO(
            $this->saleProfitCfgName,
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
        $this->saleProfitCfgService->withParams($this->saleProfitCfgDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->saleProfitCfgName);
            } else {
                $data = $this->saleProfitCfgService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->saleProfitCfgName);
            } else {
                $data = $this->saleProfitCfgService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->saleProfitCfg, $this->saleProfitCfgName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->saleProfitCfgName, $id);
        } else {
            $data = $this->saleProfitCfgService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateSaleProfitCfgRequest $request)
    {
        return $this->saleProfitCfgService->createSaleProfitCfg($request);
    }
    public function update(UpdateSaleProfitCfgRequest $request, $id)
    {
        return $this->saleProfitCfgService->updateSaleProfitCfg($id, $request);
    }
    public function destroy($id)
    {
        return $this->saleProfitCfgService->deleteSaleProfitCfg($id);
    }
}
