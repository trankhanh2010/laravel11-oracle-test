<?php

namespace App\Services\Model;

use App\DTOs\PatientDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReq\InsertServiceReqIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientRepository;

class PatientService
{
    protected $patientRepository;
    protected $params;
    public function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }
    public function withParams(PatientDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    private function getAllDataFromDatabaseTimThongTinBenhNhan()
    {
        $data = $this->patientRepository->applyJoinsTimThongTinBenhNhan();
        $data = $this->patientRepository->applyWithParamTimThongTinBenhNhan($data);
        $data = $this->patientRepository->applyIsActiveFilter($data, 1);
        $data = $this->patientRepository->applyIsDeleteFilter($data, 0);
        $data = $this->patientRepository->applyGuestFilter($data, $this->params->phone, $this->params->cccdNumber);
        $count = null;
        $this->params->orderBy = [
            'create_time' => 'desc'
        ];
        $data = $this->patientRepository->applyOrdering($data, $this->params->orderBy, []);
        $data = $this->patientRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseLayThongTinBenhNhan()
    {
        $data = $this->patientRepository->applyJoinsLayThongTinBenhNhan();
        $data = $this->patientRepository->applyWithParamLayThongTinBenhNhan($data);
        $data = $this->patientRepository->applyIsActiveFilter($data, 1);
        $data = $this->patientRepository->applyIsDeleteFilter($data, 0);
        // $data = $this->patientRepository->applyPhoneFilter($data, $this->params->phone);
        // $data = $this->patientRepository->applyCccdNumberFilter($data, $this->params->cccdNumber);
        $data = $this->patientRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $data->first();
        $data = $this->patientRepository->mergeSdaIdsToPatient($data);
        return $data;
    }
    public function handleDataBaseGetAllTimThongTinBenhNhan()
    {
        try {
            return $this->getAllDataFromDatabaseTimThongTinBenhNhan();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient'], $e);
        }
    }
    public function handleDataBaseGetAllLayThongTinBenhNhan()
    {
        try {
            return $this->getAllDataFromDatabaseLayThongTinBenhNhan();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient'], $e);
        }
    }
}
