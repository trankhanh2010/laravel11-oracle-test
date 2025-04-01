<?php

namespace App\Services\Model;

use App\DTOs\IcdListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\IcdListVView\InsertIcdListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\IcdListVViewRepository;
use Illuminate\Support\Facades\Redis;

class IcdListVViewService
{
    protected $icdListVViewRepository;
    protected $params;
    public function __construct(IcdListVViewRepository $icdListVViewRepository)
    {
        $this->icdListVViewRepository = $icdListVViewRepository;
    }
    public function withParams(IcdListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleCustomParamElasticSearch()
    {
        $data = null;
        if ($this->params->tab == 'select') {
            $data =  [
                "bool" => [
                    "filter" => [
                        ["term" => ["is_active" => 1]],
                        ["term" => ["is_delete" => 0]],
                    ],
                    "must" => [
                        [
                            "bool" => [
                                "should" => [
                                    ["wildcard" => ["icd_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["icd_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["icd_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["icd_code" => $this->params->keyword]]
                                ],
                                "minimum_should_match" => 1
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $data;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->icdListVViewRepository->applyJoins();
            $data = $this->icdListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->icdListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->icdListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->icdListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->icdListVViewRepository->applyJoins();
        $data = $this->icdListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->icdListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->icdListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->icdListVViewRepository->applyJoins()
            ->where('id', $id);
        $data = $this->icdListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->icdListVViewName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->icdListVViewName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->icdListVViewName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->icdListVViewName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_list_v_view'], $e);
        }
    }
}
