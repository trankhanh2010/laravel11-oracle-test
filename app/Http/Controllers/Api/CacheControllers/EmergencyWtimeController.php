<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EmergencyWtimeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\EmergencyWtime\CreateEmergencyWtimeRequest;
use App\Http\Requests\EmergencyWtime\UpdateEmergencyWtimeRequest;
use App\Models\HIS\EmergencyWtime;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EmergencyWtimeService;
use Illuminate\Http\Request;


class EmergencyWtimeController extends BaseApiCacheController
{
    protected $emergencyWtimeService;
    protected $emergencyWtimeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EmergencyWtimeService $emergencyWtimeService, EmergencyWtime $emergencyWtime)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->emergencyWtimeService = $emergencyWtimeService;
        $this->emergencyWtime = $emergencyWtime;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->emergencyWtime);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->emergencyWtimeDTO = new EmergencyWtimeDTO(
            $this->emergencyWtimeName,
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
        $this->emergencyWtimeService->withParams($this->emergencyWtimeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->emergencyWtimeName);
            } else {
                $data = $this->emergencyWtimeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->emergencyWtimeName);
            } else {
                $data = $this->emergencyWtimeService->handleDataBaseGetAll();
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
        $data = $this->emergencyWtimeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->emergencyWtime, $this->emergencyWtimeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->emergencyWtimeName, $id);
        } else {
            $data = $this->emergencyWtimeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
