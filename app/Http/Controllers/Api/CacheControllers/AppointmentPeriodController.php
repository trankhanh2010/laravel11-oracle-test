<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AppointmentPeriodDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AppointmentPeriod\CreateAppointmentPeriodRequest;
use App\Http\Requests\AppointmentPeriod\UpdateAppointmentPeriodRequest;
use App\Models\HIS\AppointmentPeriod;
use App\Services\Model\AppointmentPeriodService;
use Illuminate\Http\Request;


class AppointmentPeriodController extends BaseApiCacheController
{
    protected $appointmentPeriodService;
    protected $appointmentPeriodDTO;
    public function __construct(Request $request, AppointmentPeriodService $appointmentPeriodService, AppointmentPeriod $appointmentPeriod)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->appointmentPeriodService = $appointmentPeriodService;
        $this->appointmentPeriod = $appointmentPeriod;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->appointmentPeriod);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->appointmentPeriodDTO = new AppointmentPeriodDTO(
            $this->appointmentPeriodName,
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
        $this->appointmentPeriodService->withParams($this->appointmentPeriodDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null) && !$this->cache) {
            $data = $this->appointmentPeriodService->handleDataBaseSearch();
        } else {
            $data = $this->appointmentPeriodService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->appointmentPeriod, $this->appointmentPeriodName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->appointmentPeriodName, $id);
        } else {
            $data = $this->appointmentPeriodService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
