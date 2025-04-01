<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicalContractDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicalContract\CreateMedicalContractRequest;
use App\Http\Requests\MedicalContract\UpdateMedicalContractRequest;
use App\Models\HIS\MedicalContract;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicalContractService;
use Illuminate\Http\Request;


class MedicalContractController extends BaseApiCacheController
{
    protected $medicalContractService;
    protected $medicalContractDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicalContractService $medicalContractService, MedicalContract $medicalContract)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicalContractService = $medicalContractService;
        $this->medicalContract = $medicalContract;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicalContract);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicalContractDTO = new MedicalContractDTO(
            $this->medicalContractName,
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
        );
        $this->medicalContractService->withParams($this->medicalContractDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicalContractName);
            } else {
                $data = $this->medicalContractService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicalContractName);
            } else {
                $data = $this->medicalContractService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicalContract, $this->medicalContractName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicalContractName, $id);
        } else {
            $data = $this->medicalContractService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicalContractRequest $request)
    {
        return $this->medicalContractService->createMedicalContract($request);
    }
    public function update(UpdateMedicalContractRequest $request, $id)
    {
        return $this->medicalContractService->updateMedicalContract($id, $request);
    }
    public function destroy($id)
    {
        return $this->medicalContractService->deleteMedicalContract($id);
    }
}
