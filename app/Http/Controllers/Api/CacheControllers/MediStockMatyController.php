<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MediStockMatyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediStockMaty\CreateMediStockMatyRequest;
use App\Http\Requests\MediStockMaty\UpdateMediStockMatyRequest;
use App\Models\HIS\MediStockMaty;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MediStockMatyService;
use Illuminate\Http\Request;


class MediStockMatyController extends BaseApiCacheController
{
    protected $mediStockMatyService;
    protected $mediStockMatyDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MediStockMatyService $mediStockMatyService, MediStockMaty $mediStockMaty)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mediStockMatyService = $mediStockMatyService;
        $this->mediStockMaty = $mediStockMaty;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'medi_stock_code',
                'medi_stock_name',
                'service_unit_code',
                'service_unit_name',
                'material_type_code',
                'material_type_name',
                'exp_medi_stock_code',
                'exp_medi_stock_name',
            ];
            $columns = $this->getColumnsTable($this->mediStockMaty);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mediStockMatyDTO = new MediStockMatyDTO(
            $this->mediStockMatyName,
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
            $this->materialTypeId,
            $this->param,
            $this->noCache,
        );
        $this->mediStockMatyService->withParams($this->mediStockMatyDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mediStockMatyName);
            } else {
                $data = $this->mediStockMatyService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mediStockMatyName);
            } else {
                $data = $this->mediStockMatyService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mediStockMaty, $this->mediStockMatyName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mediStockMatyName, $id);
        } else {
            $data = $this->mediStockMatyService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMediStockMatyRequest $request)
    {
        return $this->mediStockMatyService->createMediStockMaty($request);
    }
}
