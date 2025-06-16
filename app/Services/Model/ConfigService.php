<?php

namespace App\Services\Model;

use App\DTOs\ConfigDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Config\InsertConfigIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ConfigRepository;
use Illuminate\Support\Facades\Redis;

class ConfigService
{
    protected $configRepository;
    protected $params;
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }
    public function withParams(ConfigDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    private function getAllDataFromDatabase()
    {
         switch ($this->params->tab) {
                case 'soNgayHenKhamMacDinh':
                    $data = $this->configRepository->getExeHisTreatmentEndAppointmentTimeDefault();
                    break;
                default:
                    $data = null;
                    break;
            }
        return ['data' => $data, 'count' => null];
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->configName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->configName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['config'], $e);
        }
    }
}
