<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DistrictDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\District\CreateDistrictRequest;
use App\Http\Requests\District\UpdateDistrictRequest;
use App\Models\SDA\District;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DistrictService;
use Illuminate\Http\Request;


class DistrictController extends BaseApiCacheController
{
    protected $districtService;
    protected $districtDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DistrictService $districtService, District $district)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->districtService = $districtService;
        $this->district = $district;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'province_code',
                'province_name',
            ];
            $columns = $this->getColumnsTable($this->district);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->districtDTO = new DistrictDTO(
            $this->districtName,
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
        $this->districtService->withParams($this->districtDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->districtName);
            } else {
                $data = $this->districtService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->districtName);
            } else {
                $data = $this->districtService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->district, $this->districtName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->districtName, $id);
        } else {
            $data = $this->districtService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDistrictRequest $request)
    {
        return $this->districtService->createDistrict($request);
    }
    public function update(UpdateDistrictRequest $request, $id)
    {
        return $this->districtService->updateDistrict($id, $request);
    }
    public function destroy($id)
    {
        return $this->districtService->deleteDistrict($id);
    }
}
