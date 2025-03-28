<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceReqSttDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceReqStt\CreateServiceReqSttRequest;
use App\Http\Requests\ServiceReqStt\UpdateServiceReqSttRequest;
use App\Models\HIS\ServiceReqStt;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceReqSttService;
use Illuminate\Http\Request;


class ServiceReqSttController extends BaseApiCacheController
{
    protected $serviceReqSttService;
    protected $serviceReqSttDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceReqSttService $serviceReqSttService, ServiceReqStt $serviceReqStt)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceReqSttService = $serviceReqSttService;
        $this->serviceReqStt = $serviceReqStt;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->serviceReqStt);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceReqSttDTO = new ServiceReqSttDTO(
            $this->serviceReqSttName,
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
        $this->serviceReqSttService->withParams($this->serviceReqSttDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceReqSttName);
            } else {
                $data = $this->serviceReqSttService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceReqSttName);
            } else {
                $data = $this->serviceReqSttService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceReqStt, $this->serviceReqSttName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceReqSttName, $id);
        } else {
            $data = $this->serviceReqSttService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
