<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\ServiceReqListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceReqListVView\CreateServiceReqListVViewRequest;
use App\Http\Requests\ServiceReqListVView\UpdateServiceReqListVViewRequest;
use App\Models\View\ServiceReqListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceReqListVViewService;
use Illuminate\Http\Request;


class ServiceReqListVViewController extends BaseApiCacheController
{
    protected $serviceReqListVViewService;
    protected $serviceReqListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceReqListVViewService $serviceReqListVViewService, ServiceReqListVView $serviceReqListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceReqListVViewService = $serviceReqListVViewService;
        $this->serviceReqListVView = $serviceReqListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->serviceReqListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceReqListVViewDTO = new ServiceReqListVViewDTO(
            $this->serviceReqListVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->groupBy,
            $this->trackingId,
            $this->treatmentId,
            $this->param,
            $this->noCache,
            $this->treatmentCode,
        );
        $this->serviceReqListVViewService->withParams($this->serviceReqListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceReqListVViewName);
            } else {
                $data = $this->serviceReqListVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceReqListVViewName);
            } else {
                $data = $this->serviceReqListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceReqListVView, $this->serviceReqListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->serviceReqListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
