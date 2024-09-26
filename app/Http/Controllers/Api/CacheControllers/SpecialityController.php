<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\SpecialityDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Speciality\CreateSpecialityRequest;
use App\Http\Requests\Speciality\UpdateSpecialityRequest;
use App\Models\HIS\Speciality;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SpecialityService;
use Illuminate\Http\Request;


class SpecialityController extends BaseApiCacheController
{
    protected $specialityService;
    protected $specialityDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SpecialityService $specialityService, Speciality $speciality)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->specialityService = $specialityService;
        $this->speciality = $speciality;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->speciality);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->specialityDTO = new SpecialityDTO(
            $this->specialityName,
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
        $this->specialityService->withParams($this->specialityDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->specialityName);
            } else {
                $data = $this->specialityService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->specialityName);
            } else {
                $data = $this->specialityService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->speciality, $this->specialityName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->specialityName, $id);
        } else {
            $data = $this->specialityService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateSpecialityRequest $request)
    {
        return $this->specialityService->createSpeciality($request);
    }
    public function update(UpdateSpecialityRequest $request, $id)
    {
        return $this->specialityService->updateSpeciality($id, $request);
    }
    public function destroy($id)
    {
        return $this->specialityService->deleteSpeciality($id);
    }
}
