<?php

namespace App\Services\Model;

use App\DTOs\SignerDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Signer\InsertSignerIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SignerRepository;
use Illuminate\Support\Facades\Redis;

class SignerService
{
    protected $signerRepository;
    protected $params;
    public function __construct(SignerRepository $signerRepository)
    {
        $this->signerRepository = $signerRepository;
    }
    public function withParams(SignerDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->signerRepository->applyJoins();
            $data = $this->signerRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->signerRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->signerRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->signerRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->signerRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['signer'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->signerRepository->applyJoins();
        $data = $this->signerRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->signerRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $count = $data->count();
        $data = $this->signerRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->signerRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->signerRepository->applyJoins()
            ->where('emr_signer.id', $id);
        $data = $this->signerRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->signerName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->signerName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['signer'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->signerName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->signerName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['signer'], $e);
        }
    }

}
