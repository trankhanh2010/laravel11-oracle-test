<?php

namespace App\Services\Model;

use App\DTOs\TreatmentFeeListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentFeeListVView\InsertTreatmentFeeListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentFeeListVViewRepository;

class TreatmentFeeListVViewService
{
    protected $treatmentFeeListVViewRepository;
    protected $params;
    public function __construct(TreatmentFeeListVViewRepository $treatmentFeeListVViewRepository)
    {
        $this->treatmentFeeListVViewRepository = $treatmentFeeListVViewRepository;
    }
    public function withParams(TreatmentFeeListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentFeeListVViewRepository->applyJoins();
            $data = $this->treatmentFeeListVViewRepository->applyWith($data);
            $data = $this->treatmentFeeListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentFeeListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentFeeListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentFeeListVViewRepository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->treatmentFeeListVViewRepository->applyToTimeFilter($data, $this->params->toTime);
            if ($this->params->start == 0) {
                // $count = $data->count();
                $count = null;
            } else {
                $count = null;
            }
            $data = $this->treatmentFeeListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentFeeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->treatmentFeeListVViewRepository->applyJoins();
            // $data = $this->treatmentFeeListVViewRepository->applyWith($data);
            if ($this->params->treatmentCode) {
                $data = $this->treatmentFeeListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            }
            if ($this->params->patientCode) {
                $data = $this->treatmentFeeListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            }
            if ($this->params->patientPhone) {
                $data = $this->treatmentFeeListVViewRepository->applyPatientPhoneFilter($data, $this->params->patientPhone);
            }
            $data = $this->treatmentFeeListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentFeeListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentFeeListVViewRepository->applyStatusFilter($data, $this->params->status);
            $data = $this->treatmentFeeListVViewRepository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->treatmentFeeListVViewRepository->applyToTimeFilter($data, $this->params->toTime);

            $data = $this->treatmentFeeListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentFeeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            if ($this->params->getAll) {
                $count = $data->count();
            } else {
                $count = null;
            }
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->treatmentFeeListVViewRepository->applyJoins()
                ->where('v_his_treatment_fee_list.id', $id);
            $data = $this->treatmentFeeListVViewRepository->applyWith($data);
            $data = $this->treatmentFeeListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentFeeListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_list_v_view'], $e);
        }
    }

    public function handleViewNoLogin()
    {
        try {
            $data = [];
            $count = null;
            if ($this->params->treatmentCode || $this->params->patientCode) {
                $data = $this->treatmentFeeListVViewRepository->applyJoins();
                if ($this->params->treatmentCode) {
                    $data = $this->treatmentFeeListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
                }
                if ($this->params->patientCode) {
                    $data = $this->treatmentFeeListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
                }
                $data = $this->treatmentFeeListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->treatmentFeeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            }
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_list_v_view'], $e);
        }
    }
}