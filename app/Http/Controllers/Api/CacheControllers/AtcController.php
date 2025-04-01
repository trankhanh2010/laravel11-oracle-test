<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AtcDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Atc\CreateAtcRequest;
use App\Http\Requests\Atc\UpdateAtcRequest;
use App\Models\HIS\Atc;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AtcService;
use Illuminate\Http\Request;


class AtcController extends BaseApiCacheController
{
    protected $atcService;
    protected $atcDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AtcService $atcService, Atc $atc)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->atcService = $atcService;
        $this->atc = $atc;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->atc);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->atcDTO = new AtcDTO(
            $this->atcName,
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
        $this->atcService->withParams($this->atcDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->atcName);
            } else {
                $data = $this->atcService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->atcName);
            } else {
                $data = $this->atcService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->atc, $this->atcName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->atcName, $id);
        } else {
            $data = $this->atcService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAtcRequest $request)
    {
        return $this->atcService->createAtc($request);
    }
    public function update(UpdateAtcRequest $request, $id)
    {
        return $this->atcService->updateAtc($id, $request);
    }
    public function destroy($id)
    {
        return $this->atcService->deleteAtc($id);
    }
}
