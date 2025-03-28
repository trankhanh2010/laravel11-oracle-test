<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicinePatyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicinePaty\CreateMedicinePatyRequest;
use App\Http\Requests\MedicinePaty\UpdateMedicinePatyRequest;
use App\Models\HIS\MedicinePaty;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicinePatyService;
use Illuminate\Http\Request;


class MedicinePatyController extends BaseApiCacheController
{
    protected $medicinePatyService;
    protected $medicinePatyDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicinePatyService $medicinePatyService, MedicinePaty $medicinePaty)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicinePatyService = $medicinePatyService;
        $this->medicinePaty = $medicinePaty;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'medicine_type_code',
                'medicine_type_name',
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
            $columns = $this->getColumnsTable($this->medicinePaty);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicinePatyDTO = new MedicinePatyDTO(
            $this->medicinePatyName,
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
        $this->medicinePatyService->withParams($this->medicinePatyDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicinePatyName);
            } else {
                $data = $this->medicinePatyService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicinePatyName);
            } else {
                $data = $this->medicinePatyService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicinePaty, $this->medicinePatyName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicinePatyName, $id);
        } else {
            $data = $this->medicinePatyService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicinePatyRequest $request)
    {
        return $this->medicinePatyService->createMedicinePaty($request);
    }
    public function update(UpdateMedicinePatyRequest $request, $id)
    {
        return $this->medicinePatyService->updateMedicinePaty($id, $request);
    }
    public function destroy($id)
    {
        return $this->medicinePatyService->deleteMedicinePaty($id);
    }
}
