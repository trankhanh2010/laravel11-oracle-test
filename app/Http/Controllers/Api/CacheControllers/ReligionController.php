<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ReligionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Religion\CreateReligionRequest;
use App\Http\Requests\Religion\UpdateReligionRequest;
use App\Models\SDA\Religion;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ReligionService;
use Illuminate\Http\Request;


class ReligionController extends BaseApiCacheController
{
    protected $religionService;
    protected $religionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ReligionService $religionService, Religion $religion)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->religionService = $religionService;
        $this->religion = $religion;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->religion);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->religionDTO = new ReligionDTO(
            $this->religionName,
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
        $this->religionService->withParams($this->religionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->religionName);
            } else {
                $data = $this->religionService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->religionName);
            } else {
                $data = $this->religionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->religion, $this->religionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->religionName, $id);
        } else {
            $data = $this->religionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateReligionRequest $request)
    {
        return $this->religionService->createReligion($request);
    }
    public function update(UpdateReligionRequest $request, $id)
    {
        return $this->religionService->updateReligion($id, $request);
    }
    public function destroy($id)
    {
        return $this->religionService->deleteReligion($id);
    }
}
