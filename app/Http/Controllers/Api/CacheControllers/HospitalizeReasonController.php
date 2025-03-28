<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\HospitalizeReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\HospitalizeReason\CreateHospitalizeReasonRequest;
use App\Http\Requests\HospitalizeReason\UpdateHospitalizeReasonRequest;
use App\Models\HIS\HospitalizeReason;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\HospitalizeReasonService;
use Illuminate\Http\Request;


class HospitalizeReasonController extends BaseApiCacheController
{
    protected $hospitalizeReasonService;
    protected $hospitalizeReasonDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, HospitalizeReasonService $hospitalizeReasonService, HospitalizeReason $hospitalizeReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->hospitalizeReasonService = $hospitalizeReasonService;
        $this->hospitalizeReason = $hospitalizeReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->hospitalizeReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->hospitalizeReasonDTO = new HospitalizeReasonDTO(
            $this->hospitalizeReasonName,
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
        );
        $this->hospitalizeReasonService->withParams($this->hospitalizeReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->hospitalizeReasonName);
            } else {
                $data = $this->hospitalizeReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->hospitalizeReasonName);
            } else {
                $data = $this->hospitalizeReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->hospitalizeReason, $this->hospitalizeReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->hospitalizeReasonName, $id);
        } else {
            $data = $this->hospitalizeReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateHospitalizeReasonRequest $request)
    {
        return $this->hospitalizeReasonService->createHospitalizeReason($request);
    }
    public function update(UpdateHospitalizeReasonRequest $request, $id)
    {
        return $this->hospitalizeReasonService->updateHospitalizeReason($id, $request);
    }
    public function destroy($id)
    {
        return $this->hospitalizeReasonService->deleteHospitalizeReason($id);
    }
}
