<?php

namespace App\Services\Model;

use App\DTOs\AppointmentPeriodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AppointmentPeriod\InsertAppointmentPeriodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AppointmentPeriodRepository;
use Illuminate\Support\Facades\Redis;

class AppointmentPeriodService
{
    protected $appointmentPeriodRepository;
    protected $params;
    public function __construct(AppointmentPeriodRepository $appointmentPeriodRepository)
    {
        $this->appointmentPeriodRepository = $appointmentPeriodRepository;
    }
    public function withParams(AppointmentPeriodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->appointmentPeriodRepository->applyJoins();
            $data = $this->appointmentPeriodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->appointmentPeriodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->appointmentPeriodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->appointmentPeriodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['appointment_period'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->appointmentPeriodRepository->applyJoins();
        $data = $this->appointmentPeriodRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->appointmentPeriodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->appointmentPeriodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->appointmentPeriodRepository->applyJoins()
            ->where('his_appointment_period.id', $id);
        $data = $this->appointmentPeriodRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->appointmentPeriodName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->appointmentPeriodName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['appointment_period'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->appointmentPeriodName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->appointmentPeriodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['appointment_period'], $e);
        }
    }

    // public function createAppointmentPeriod($request)
    // {
    //     try {
    //         $data = $this->appointmentPeriodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertAppointmentPeriodIndex($data, $this->params->appointmentPeriodName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->appointmentPeriodName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['appointment_period'], $e);
    //     }
    // }

    // public function updateAppointmentPeriod($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->appointmentPeriodRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->appointmentPeriodRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertAppointmentPeriodIndex($data, $this->params->appointmentPeriodName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->appointmentPeriodName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['appointment_period'], $e);
    //     }
    // }

    // public function deleteAppointmentPeriod($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->appointmentPeriodRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->appointmentPeriodRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->appointmentPeriodName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->appointmentPeriodName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['appointment_period'], $e);
    //     }
    // }
}
