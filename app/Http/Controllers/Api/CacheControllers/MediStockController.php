<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MediStockDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediStock\CreateMediStockRequest;
use App\Http\Requests\MediStock\UpdateMediStockRequest;
use App\Models\HIS\MediStock;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MediStockService;
use Illuminate\Http\Request;


class MediStockController extends BaseApiCacheController
{
    protected $mediStockService;
    protected $mediStockDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MediStockService $mediStockService, MediStock $mediStock)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mediStockService = $mediStockService;
        $this->mediStock = $mediStock;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_name',
                'department_code',
                'room_type_name',
                'room_type_code',
                'parent_name',
                'parent_code'
            ];
            $columns = $this->getColumnsTable($this->mediStock);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mediStockDTO = new MediStockDTO(
            $this->mediStockName,
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
        );
        $this->mediStockService->withParams($this->mediStockDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mediStockName);
            } else {
                $data = $this->mediStockService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mediStockName);
            } else {
                $data = $this->mediStockService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mediStock, $this->mediStockName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mediStockName, $id);
        } else {
            $data = $this->mediStockService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMediStockRequest $request)
    {
        return $this->mediStockService->createMediStock($request);
    }
    public function update(UpdateMediStockRequest $request, $id)
    {
        return $this->mediStockService->updateMediStock($id, $request);
    }
    public function destroy($id)
    {
        return $this->mediStockService->deleteMediStock($id);
    }
}
