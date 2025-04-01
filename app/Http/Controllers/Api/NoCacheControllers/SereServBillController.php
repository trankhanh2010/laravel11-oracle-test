<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServBillDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServBill\CreateSereServBillRequest;
use App\Http\Requests\SereServBill\UpdateSereServBillRequest;
use App\Models\HIS\SereServBill;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServBillService;
use Illuminate\Http\Request;


class SereServBillController extends BaseApiCacheController
{
    protected $sereServBillService;
    protected $sereServBillDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServBillService $sereServBillService, SereServBill $sereServBill)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServBillService = $sereServBillService;
        $this->sereServBill = $sereServBill;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServBill);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServBillDTO = new SereServBillDTO(
            $this->sereServBillName,
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
            $this->billId,
            $this->billCode,
            $this->param,
            $this->noCache,
        );
        $this->sereServBillService->withParams($this->sereServBillDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->sereServBillName);
            } else {
                $data = $this->sereServBillService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->sereServBillName);
            } else {
                $data = $this->sereServBillService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServBill, $this->sereServBillName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServBillName, $id);
        } else {
            $data = $this->sereServBillService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
