<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Bed\CreateBedRequest;
use App\Http\Requests\Bed\UpdateBedRequest;
use App\Models\HIS\Bed;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BedService;
use Illuminate\Http\Request;

class BedController extends BaseApiCacheController
{
    protected $bedService;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BedService $bedService, Bed $bed)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bedService = $bedService;
        $this->bed = $bed;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'bed_type_name',
                'bed_type_code',
                'bed_room_name',
                'bed_room_code',
                'department_name',
                'department_code',
            ];
            $columns = $this->getColumnsTable($this->bed);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
            $this->orderByString = arrayToCustomString($this->orderBy);
        }
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
                if ($this->elasticSearchType != null) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->bedName);
                } else {
                    $data = $this->bedService->handleDataBaseSearch($keyword, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
                }
            } else {
                if ($this->elastic) {
                    $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bedName);
                } else {
                    $data = $this->bedService->handleDataBaseGetAll($this->bedName, $this->isActive, $this->orderBy, $this->orderByJoin, $this->getAll, $this->start, $this->limit);
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
        } catch (\Throwable $e) {
            logError($e);
            sendErrorToTelegram($e);
            return return500Error($e->getMessage());
        }
    }

    public function show($id)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        try {
            if ($id !== null) {
                $validationError = $this->validateAndCheckId($id, $this->bed, $this->bedName);
                if ($validationError) {
                    return $validationError;
                }
            }
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bedName, $id);
            } else {
                $data = $this->bedService->handleDataBaseGetWithId($this->bedName, $id, $this->isActive);
            }
            $paramReturn = [
                $this->idName => $id,
                $this->isActiveName => $this->isActive,
            ];
            return returnDataSuccess($paramReturn, $data);
        } catch (\Throwable $e) {
            logError($e);
            sendErrorToTelegram($e);
            return return500Error($e->getMessage());
        }
    }
    public function store(CreateBedRequest $request)
    {
        try {
            return $this->bedService->createBed($request, $this->time, $this->appCreator, $this->appModifier);
        } catch (\Throwable $e) {
            logError($e);
            sendErrorToTelegram($e);
            return return500Error($e->getMessage());
        }
    }
    public function update(UpdateBedRequest $request, $id)
    {
        try {
            return $this->bedService->updateBed($this->bedName, $id, $request, $this->time, $this->appModifier);
        } catch (\Throwable $e) {
            logError($e);
            sendErrorToTelegram($e);
            return return500Error($e->getMessage());
        }
    }
    public function destroy($id)
    {
        try {
            return $this->bedService->deleteBed($this->bedName, $id);
        } catch (\Throwable $e) {
            logError($e);
            sendErrorToTelegram($e);
            return return500Error($e->getMessage());
        }
    }
}
