<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ImpSourceDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ImpSource\CreateImpSourceRequest;
use App\Http\Requests\ImpSource\UpdateImpSourceRequest;
use App\Models\HIS\ImpSource;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ImpSourceService;
use Illuminate\Http\Request;


class ImpSourceController extends BaseApiCacheController
{
    protected $impSourceService;
    protected $impSourceDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ImpSourceService $impSourceService, ImpSource $impSource)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->impSourceService = $impSourceService;
        $this->impSource = $impSource;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->impSource);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->impSourceDTO = new ImpSourceDTO(
            $this->impSourceName,
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
        $this->impSourceService->withParams($this->impSourceDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->impSourceName);
            } else {
                $data = $this->impSourceService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->impSourceName);
            } else {
                $data = $this->impSourceService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->impSource, $this->impSourceName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->impSourceName, $id);
        } else {
            $data = $this->impSourceService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateImpSourceRequest $request)
    {
        return $this->impSourceService->createImpSource($request);
    }
    public function update(UpdateImpSourceRequest $request, $id)
    {
        return $this->impSourceService->updateImpSource($id, $request);
    }
    public function destroy($id)
    {
        return $this->impSourceService->deleteImpSource($id);
    }
}
