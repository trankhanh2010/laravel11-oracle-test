<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\NationalDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\National\CreateNationalRequest;
use App\Http\Requests\National\UpdateNationalRequest;
use App\Models\SDA\National;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\NationalService;
use Illuminate\Http\Request;


class NationalController extends BaseApiCacheController
{
    protected $nationalService;
    protected $nationalDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, NationalService $nationalService, National $national)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->nationalService = $nationalService;
        $this->national = $national;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->national);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->nationalDTO = new NationalDTO(
            $this->nationalName,
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
        $this->nationalService->withParams($this->nationalDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->nationalName);
            } else {
                $data = $this->nationalService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->nationalName);
            } else {
                $data = $this->nationalService->handleDataBaseGetAll();
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
    public function guest()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->nationalService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->national, $this->nationalName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->nationalName, $id);
        } else {
            $data = $this->nationalService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateNationalRequest $request)
    {
        return $this->nationalService->createNational($request);
    }
    public function update(UpdateNationalRequest $request, $id)
    {
        return $this->nationalService->updateNational($id, $request);
    }
    public function destroy($id)
    {
        return $this->nationalService->deleteNational($id);
    }
}
