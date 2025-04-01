<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentService;
use Illuminate\Http\Request;


class TreatmentWithPatientTypeInfoSdoController extends BaseApiCacheController
{
    protected $treatmentService;
    protected $treatmentDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentService $treatmentService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentService = $treatmentService;
        // Kiểm tra tên trường trong bảng
        // if ($this->orderBy != null) {
        //     $this->orderByJoin = [
        //     ];
        //     $columns = $this->getColumnsTable($this->treatmentWithPatientTypeInfoSdo);
        //     $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        // }
        // Thêm tham số vào service
        $this->treatmentDTO = new TreatmentDTO(
            $this->treatmentWithPatientTypeInfoSdoName,
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
            $this->param,
            $this->noCache,
        );
        $this->treatmentService->withParams($this->treatmentDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->treatmentService->handleDataBaseTreatmentWithPatientTypeInfoSdoGetAll($this->treatmentId);
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
            $validationError = $this->validateAndCheckId($id, $this->treatment, $this->treatmentName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentWithPatientTypeInfoSdoName, $id);
        } else {
            $data = $this->treatmentService->handleDataBaseTreatmentWithPatientTypeInfoSdoGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
