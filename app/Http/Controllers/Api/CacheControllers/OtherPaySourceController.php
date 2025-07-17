<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\OtherPaySourceDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\OtherPaySource\CreateOtherPaySourceRequest;
use App\Http\Requests\OtherPaySource\UpdateOtherPaySourceRequest;
use App\Models\HIS\OtherPaySource;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\OtherPaySourceService;
use Illuminate\Http\Request;


class OtherPaySourceController extends BaseApiCacheController
{
    protected $otherPaySourceService;
    protected $otherPaySourceDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, OtherPaySourceService $otherPaySourceService, OtherPaySource $otherPaySource)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->otherPaySourceService = $otherPaySourceService;
        $this->otherPaySource = $otherPaySource;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->otherPaySource);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->otherPaySourceDTO = new OtherPaySourceDTO(
            $this->otherPaySourceName,
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
        $this->otherPaySourceService->withParams($this->otherPaySourceDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->otherPaySourceName);
            } else {
                $data = $this->otherPaySourceService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->otherPaySourceName);
            } else {
                $data = $this->otherPaySourceService->handleDataBaseGetAll();
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
                $data = $this->otherPaySourceService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->otherPaySource, $this->otherPaySourceName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->otherPaySourceName, $id);
        } else {
            $data = $this->otherPaySourceService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateOtherPaySourceRequest $request)
    {
        return $this->otherPaySourceService->createOtherPaySource($request);
    }
    public function update(UpdateOtherPaySourceRequest $request, $id)
    {
        return $this->otherPaySourceService->updateOtherPaySource($id, $request);
    }
    public function destroy($id)
    {
        return $this->otherPaySourceService->deleteOtherPaySource($id);
    }
}
