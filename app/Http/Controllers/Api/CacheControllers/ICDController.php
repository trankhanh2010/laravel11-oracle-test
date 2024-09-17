<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\IcdDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Icd\CreateIcdRequest;
use App\Http\Requests\Icd\UpdateIcdRequest;
use App\Models\HIS\Icd;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\IcdService;
use Illuminate\Http\Request;


class IcdController extends BaseApiCacheController
{
    protected $icdService;
    protected $icdDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, IcdService $icdService, Icd $icd)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->icdService = $icdService;
        $this->icd = $icd;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->icd);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->icdDTO = new IcdDTO(
            $this->icdName,
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
        $this->icdService->withParams($this->icdDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->icdName);
            } else {
                $data = $this->icdService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->icdName);
            } else {
                $data = $this->icdService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->icd, $this->icdName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->icdName, $id);
        } else {
            $data = $this->icdService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateIcdRequest $request)
    {
        return $this->icdService->createIcd($request);
    }
    public function update(UpdateIcdRequest $request, $id)
    {
        return $this->icdService->updateIcd($id, $request);
    }
    public function destroy($id)
    {
        return $this->icdService->deleteIcd($id);
    }
}
