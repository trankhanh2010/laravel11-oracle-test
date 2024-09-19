<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MediOrgDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediOrg\CreateMediOrgRequest;
use App\Http\Requests\MediOrg\UpdateMediOrgRequest;
use App\Models\HIS\MediOrg;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MediOrgService;
use Illuminate\Http\Request;


class MediOrgController extends BaseApiCacheController
{
    protected $mediOrgService;
    protected $mediOrgDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MediOrgService $mediOrgService, MediOrg $mediOrg)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mediOrgService = $mediOrgService;
        $this->mediOrg = $mediOrg;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->mediOrg);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mediOrgDTO = new MediOrgDTO(
            $this->mediOrgName,
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
        $this->mediOrgService->withParams($this->mediOrgDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mediOrgName);
            } else {
                $data = $this->mediOrgService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mediOrgName);
            } else {
                $data = $this->mediOrgService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mediOrg, $this->mediOrgName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mediOrgName, $id);
        } else {
            $data = $this->mediOrgService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMediOrgRequest $request)
    {
        return $this->mediOrgService->createMediOrg($request);
    }
    public function update(UpdateMediOrgRequest $request, $id)
    {
        return $this->mediOrgService->updateMediOrg($id, $request);
    }
    public function destroy($id)
    {
        return $this->mediOrgService->deleteMediOrg($id);
    }
}
