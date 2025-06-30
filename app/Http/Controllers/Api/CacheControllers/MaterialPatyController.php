<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MaterialPatyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MaterialPaty\CreateMaterialPatyRequest;
use App\Http\Requests\MaterialPaty\UpdateMaterialPatyRequest;
use App\Models\HIS\MaterialPaty;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MaterialPatyService;
use Illuminate\Http\Request;


class MaterialPatyController extends BaseApiCacheController
{
    protected $materialPatyService;
    protected $materialPatyDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MaterialPatyService $materialPatyService, MaterialPaty $materialPaty)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->materialPatyService = $materialPatyService;
        $this->materialPaty = $materialPaty;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'material_type_code',
                'material_type_name',
                'patient_type_code',
                'patient_type_name',
                'contract_price',
                'tax_ratio',
                'expired_date',
                'tdl_bid_number',
                'tdl_bid_num_order',
                'imp_time',
                'imp_vat_ratio',
                'imp_price',
                'vir_imp_price',
                'internal_price'
            ];
            $columns = $this->getColumnsTable($this->materialPaty);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->materialPatyDTO = new MaterialPatyDTO(
            $this->materialPatyName,
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
            $this->noCache,
            $this->tab,
        );
        $this->materialPatyService->withParams($this->materialPatyDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->materialPatyName);
            } else {
                $data = $this->materialPatyService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->materialPatyName);
            } else {
                $data = $this->materialPatyService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->materialPaty, $this->materialPatyName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->materialPatyName, $id);
        } else {
            $data = $this->materialPatyService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    // public function store(CreateMaterialPatyRequest $request)
    // {
    //     return $this->materialPatyService->createMaterialPaty($request);
    // }
    // public function update(UpdateMaterialPatyRequest $request, $id)
    // {
    //     return $this->materialPatyService->updateMaterialPaty($id, $request);
    // }
    // public function destroy($id)
    // {
    //     return $this->materialPatyService->deleteMaterialPaty($id);
    // }
}
