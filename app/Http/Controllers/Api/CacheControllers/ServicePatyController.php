<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServicePatyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServicePaty\CreateServicePatyRequest;
use App\Http\Requests\ServicePaty\UpdateServicePatyRequest;
use App\Models\HIS\ServicePaty;
use App\Models\HIS\Treatment;
use App\Services\Model\ServicePatyService;
use Illuminate\Http\Request;


class ServicePatyController extends BaseApiCacheController
{
    protected $servicePatyService;
    protected $servicePatyDTO;
    protected $treatment;
    public function __construct(
        Request $request,
        ServicePatyService $servicePatyService,
        ServicePaty $servicePaty,
        Treatment $treatment,
    ) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->servicePatyService = $servicePatyService;
        $this->servicePaty = $servicePaty;
        $this->treatment = $treatment;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'service_name',
                'service_code',
                'service_type_id',
                'patient_type_name',
                'patient_type_code',
                'branch_name',
                'branch_code',
                'package_name',
                'package_code',
                'service_type_name',
                'service_type_code',
            ];
            $columns = $this->getColumnsTable($this->servicePaty);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->servicePatyDTO = new ServicePatyDTO(
            $this->servicePatyName,
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
            $this->serviceTypeIds,
            $this->patientTypeIds,
            $this->serviceId,
            $this->packageId,
            $this->effective,
            $this->param,
            $this->noCache,
            $this->tab,
        );
        $this->servicePatyService->withParams($this->servicePatyDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($this->tab == 'donGiaVienPhi') {
            if (!$this->serviceId) {
                $this->errors[$this->serviceIdName] = "Thiếu dịch vụ chỉ định!";
            }
            if (!$this->treatmentId) {
                $this->errors[$this->treatmentIdName] = "Thiếu lần điều trị!";
            }
            $treatmentData = $this->treatment->find($this->treatmentId);
            if (!$treatmentData) {
                $this->errors[$this->treatmentIdName] = "Không tìm thấy lần điều trị!";
            }
            if ($this->checkParam()) {
                return $this->checkParam();
            }
            $data = $this->servicePatyService->getDonGiaVienPhi($this->serviceId, $treatmentData->in_time);
        } else {
            $data = $this->servicePatyService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->servicePaty, $this->servicePatyName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->servicePatyService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServicePatyRequest $request)
    {
        return $this->servicePatyService->createServicePaty($request);
    }
    public function update(UpdateServicePatyRequest $request, $id)
    {
        return $this->servicePatyService->updateServicePaty($id, $request);
    }
    public function destroy($id)
    {
        return $this->servicePatyService->deleteServicePaty($id);
    }
}
