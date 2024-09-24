<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ProvinceDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Province\CreateProvinceRequest;
use App\Http\Requests\Province\UpdateProvinceRequest;
use App\Models\SDA\Province;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ProvinceService;
use Illuminate\Http\Request;


class ProvinceController extends BaseApiCacheController
{
    protected $provinceService;
    protected $provinceDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ProvinceService $provinceService, Province $province)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->provinceService = $provinceService;
        $this->province = $province;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->province);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->provinceDTO = new ProvinceDTO(
            $this->provinceName,
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
        $this->provinceService->withParams($this->provinceDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->provinceName);
            } else {
                $data = $this->provinceService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->provinceName);
            } else {
                $data = $this->provinceService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->province, $this->provinceName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->provinceName, $id);
        } else {
            $data = $this->provinceService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateProvinceRequest $request)
    {
        return $this->provinceService->createProvince($request);
    }
    public function update(UpdateProvinceRequest $request, $id)
    {
        return $this->provinceService->updateProvince($id, $request);
    }
    public function destroy($id)
    {
        return $this->provinceService->deleteProvince($id);
    }
}
