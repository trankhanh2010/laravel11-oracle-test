<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\ServiceReqLViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceReqLView\CreateServiceReqLViewRequest;
use App\Http\Requests\ServiceReqLView\UpdateServiceReqLViewRequest;
use App\Models\View\ServiceReqLView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceReqLViewService;
use Illuminate\Http\Request;


class ServiceReqLViewController extends BaseApiCacheController
{
    protected $serviceReqLViewService;
    protected $serviceReqLViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceReqLViewService $serviceReqLViewService, ServiceReqLView $serviceReqLView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceReqLViewService = $serviceReqLViewService;
        $this->serviceReqLView = $serviceReqLView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->serviceReqLView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceReqLViewDTO = new ServiceReqLViewDTO(
            $this->serviceReqLViewName,
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
            $this->serviceReqSttIds,
            $this->notInServiceReqTypeIds,
            $this->tdlPatientTypeIds,
            $this->executeRoomId,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->hasExecute,
            $this->isNotKskRequriedAprovalOrIsKskApprove,
            $this->param,
        );
        $this->serviceReqLViewService->withParams($this->serviceReqLViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceReqLViewName);
            } else {
                $data = $this->serviceReqLViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceReqLViewName);
            } else {
                $data = $this->serviceReqLViewService->handleDataBaseGetAll();
            }
        }
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->isDeleteName => $this->isDelete,
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
            $validationError = $this->validateAndCheckId($id, $this->serviceReqLView, $this->serviceReqLViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceReqLViewName, $id);
        } else {
            $data = $this->serviceReqLViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
