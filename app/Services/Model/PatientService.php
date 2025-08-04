<?php

namespace App\Services\Model;

use App\DTOs\PatientDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReq\InsertServiceReqIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientRepository;
use Illuminate\Support\Facades\Redis;

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
        // Cache tạm 5 phút
        try {
            $cacheKey = $this->params->patientName . '_' . $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->patientName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, 300, function () {
                return $this->getAllDataFromDatabaseTimThongTinBenhNhan();
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
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
