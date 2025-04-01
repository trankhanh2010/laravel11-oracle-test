<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TranPatiTechDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TranPatiTech\CreateTranPatiTechRequest;
use App\Http\Requests\TranPatiTech\UpdateTranPatiTechRequest;
use App\Models\HIS\TranPatiTech;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TranPatiTechService;
use Illuminate\Http\Request;


class TranPatiTechController extends BaseApiCacheController
{
    protected $tranPatiTechService;
    protected $tranPatiTechDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TranPatiTechService $tranPatiTechService, TranPatiTech $tranPatiTech)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->tranPatiTechService = $tranPatiTechService;
        $this->tranPatiTech = $tranPatiTech;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->tranPatiTech);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->tranPatiTechDTO = new TranPatiTechDTO(
            $this->tranPatiTechName,
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
        $this->tranPatiTechService->withParams($this->tranPatiTechDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->tranPatiTechName);
            } else {
                $data = $this->tranPatiTechService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->tranPatiTechName);
            } else {
                $data = $this->tranPatiTechService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->tranPatiTech, $this->tranPatiTechName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->tranPatiTechName, $id);
        } else {
            $data = $this->tranPatiTechService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTranPatiTechRequest $request)
    {
        return $this->tranPatiTechService->createTranPatiTech($request);
    }
    public function update(UpdateTranPatiTechRequest $request, $id)
    {
        return $this->tranPatiTechService->updateTranPatiTech($id, $request);
    }
    public function destroy($id)
    {
        return $this->tranPatiTechService->deleteTranPatiTech($id);
    }
}
