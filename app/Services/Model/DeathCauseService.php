<?php

namespace App\Services\Model;

use App\DTOs\DeathCauseDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DeathCause\InsertDeathCauseIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DeathCauseRepository;
use Illuminate\Support\Facades\Redis;

class DeathCauseService 
{
    protected $deathCauseRepository;
    protected $params;
    public function __construct(DeathCauseRepository $deathCauseRepository)
    {
        $this->deathCauseRepository = $deathCauseRepository;
    }
    public function withParams(DeathCauseDTO $params)
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
                                    ["wildcard" => ["death_cause_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["death_cause_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["death_cause_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["death_cause_code" => $this->params->keyword]]
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
            $data = $this->deathCauseRepository->applyJoins();
            $data = $this->deathCauseRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->deathCauseRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->deathCauseRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->deathCauseRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_cause'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->deathCauseName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->deathCauseName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->deathCauseRepository->applyJoins();
                $data = $this->deathCauseRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->deathCauseRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->deathCauseRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_cause'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->deathCauseName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->deathCauseRepository->applyJoins()
                    ->where('his_death_cause.id', $id);
                $data = $this->deathCauseRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_cause'], $e);
        }
    }

 
}
