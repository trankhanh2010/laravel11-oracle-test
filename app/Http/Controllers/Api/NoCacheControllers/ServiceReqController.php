<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\ServiceReqDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceReq\CreateServiceReqRequest;
use App\Http\Requests\ServiceReq\UpdateServiceReqRequest;
use App\Models\HIS\ServiceReq;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceReqService;
use Illuminate\Http\Request;


class ServiceReqController extends BaseApiCacheController
{
    protected $serviceReqService;
    protected $serviceReqDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceReqService $serviceReqService, ServiceReq $serviceReq)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceReqService = $serviceReqService;
        $this->serviceReq = $serviceReq;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'is_pause',
                'department_id',
                'room_type_id',
                'g_code',
                'room_type_code',
                'room_type_name',
                'branch_id',
                'department_code',
                'department_name',
                'branch_code',
                'branch_name',
                'hein_medi_org_code',
                'room_name',
                'room_code',
            ];
            $columns = $this->getColumnsTable($this->serviceReq);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceReqDTO = new ServiceReqDTO(
            $this->serviceReqName,
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
        );
        $this->serviceReqService->withParams($this->serviceReqDTO);
    }
    public function indexLView(Request $request)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        // Kiểm tra xem User có quyền xem execute_room không
        // if ($this->executeRoomId != null) {
        //     if (!view_service_req($this->executeRoomId, $request->bearerToken(), $this->time)) {
        //         return return403();
        //     }
        // } else {
        //     return return400('Thiếu ExecuteRoomId!');
        // }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceReqName);
            } else {
                $data = $this->serviceReqService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceReqName);
            } else {
                $data = $this->serviceReqService->handleDataBaseGetAll();
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

    public function showLView($id)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($id !== null) {
            $validationError = $this->validateAndCheckId($id, $this->serviceReq, $this->serviceReqName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceReqName, $id);
        } else {
            $data = $this->serviceReqService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
