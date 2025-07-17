<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\WorkPlaceDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\WorkPlace\CreateWorkPlaceRequest;
use App\Http\Requests\WorkPlace\UpdateWorkPlaceRequest;
use App\Models\HIS\WorkPlace;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\WorkPlaceService;
use Illuminate\Http\Request;


class WorkPlaceController extends BaseApiCacheController
{
    protected $workPlaceService;
    protected $workPlaceDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, WorkPlaceService $workPlaceService, WorkPlace $workPlace)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->workPlaceService = $workPlaceService;
        $this->workPlace = $workPlace;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->workPlace);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->workPlaceDTO = new WorkPlaceDTO(
            $this->workPlaceName,
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
        $this->workPlaceService->withParams($this->workPlaceDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->workPlaceName);
            } else {
                $data = $this->workPlaceService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->workPlaceName);
            } else {
                $data = $this->workPlaceService->handleDataBaseGetAll();
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
        $data = $this->workPlaceService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->workPlace, $this->workPlaceName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->workPlaceName, $id);
        } else {
            $data = $this->workPlaceService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateWorkPlaceRequest $request)
    {
        return $this->workPlaceService->createWorkPlace($request);
    }
    public function update(UpdateWorkPlaceRequest $request, $id)
    {
        return $this->workPlaceService->updateWorkPlace($id, $request);
    }
    public function destroy($id)
    {
        return $this->workPlaceService->deleteWorkPlace($id);
    }
}
