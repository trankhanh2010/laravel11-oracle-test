<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\SupplierDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Supplier\CreateSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\HIS\Supplier;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SupplierService;
use Illuminate\Http\Request;


class SupplierController extends BaseApiCacheController
{
    protected $supplierService;
    protected $supplierDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SupplierService $supplierService, Supplier $supplier)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->supplierService = $supplierService;
        $this->supplier = $supplier;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->supplier);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->supplierDTO = new SupplierDTO(
            $this->supplierName,
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
        $this->supplierService->withParams($this->supplierDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->supplierName);
            } else {
                $data = $this->supplierService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->supplierName);
            } else {
                $data = $this->supplierService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->supplier, $this->supplierName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->supplierName, $id);
        } else {
            $data = $this->supplierService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateSupplierRequest $request)
    {
        return $this->supplierService->createSupplier($request);
    }
    public function update(UpdateSupplierRequest $request, $id)
    {
        return $this->supplierService->updateSupplier($id, $request);
    }
    public function destroy($id)
    {
        return $this->supplierService->deleteSupplier($id);
    }
}
