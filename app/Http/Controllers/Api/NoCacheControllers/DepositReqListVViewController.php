<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\DepositReqListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DepositReqListVView\CreateDepositReqListVViewRequest;
use App\Http\Requests\DepositReqListVView\UpdateDepositReqListVViewRequest;
use App\Models\View\DepositReqListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DepositReqListVViewService;
use Illuminate\Http\Request;


class DepositReqListVViewController extends BaseApiCacheController
{
    protected $depositReqListVViewService;
    protected $depositReqListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DepositReqListVViewService $depositReqListVViewService, DepositReqListVView $depositReqListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->depositReqListVViewService = $depositReqListVViewService;
        $this->depositReqListVView = $depositReqListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->depositReqListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->depositReqListVViewDTO = new DepositReqListVViewDTO(
            $this->depositReqListVViewName,
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
            $this->isDeposit,
            $this->treatmentId,
            $this->param,
            $this->noCache,
            $this->depositReqCode,
        );
        $this->depositReqListVViewService->withParams($this->depositReqListVViewDTO);
    }
    public function index()
    {
        if($this->treatmentId == null && $this->depositReqCode == null){
            $this->errors[$this->treatmentIdName] = 'Thiếu Id điều trị hoặc Code YCTU!';
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->depositReqListVViewName);
            } else {
                $data = $this->depositReqListVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->depositReqListVViewName);
            } else {
                $data = $this->depositReqListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->depositReqListVView, $this->depositReqListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->depositReqListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
