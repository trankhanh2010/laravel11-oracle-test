<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MediStockMetyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediStockMety\CreateMediStockMetyRequest;
use App\Http\Requests\MediStockMety\UpdateMediStockMetyRequest;
use App\Models\HIS\MediStockMety;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MediStockMetyService;
use Illuminate\Http\Request;


class MediStockMetyController extends BaseApiCacheController
{
    protected $mediStockMetyService;
    protected $mediStockMetyDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MediStockMetyService $mediStockMetyService, MediStockMety $mediStockMety)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mediStockMetyService = $mediStockMetyService;
        $this->mediStockMety = $mediStockMety;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'medi_stock_code',
                'medi_stock_name',
                'service_unit_code',
                'service_unit_name',
                'medicine_type_code',
                'medicine_type_name',
                'concentra',
                'register_number',
                'active_ingr_bhyt_code',
                'active_ingr_bhyt_name',
                'distributed_amount',
                'exp_medi_stock_code',
                'exp_medi_stock_name',
            ];
            $columns = $this->getColumnsTable($this->mediStockMety);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mediStockMetyDTO = new MediStockMetyDTO(
            $this->mediStockMetyName,
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
            $this->mediStockId,
            $this->medicineTypeId,
            $this->param,
        );
        $this->mediStockMetyService->withParams($this->mediStockMetyDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mediStockMetyName);
            } else {
                $data = $this->mediStockMetyService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mediStockMetyName);
            } else {
                $data = $this->mediStockMetyService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mediStockMety, $this->mediStockMetyName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mediStockMetyName, $id);
        } else {
            $data = $this->mediStockMetyService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMediStockMetyRequest $request)
    {
        return $this->mediStockMetyService->createMediStockMety($request);
    }
}
