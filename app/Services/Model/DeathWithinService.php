<?php

namespace App\Services\Model;

use App\DTOs\DeathWithinDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DeathWithin\InsertDeathWithinIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DeathWithinRepository;
use Illuminate\Support\Facades\Redis;

class DeathWithinService
{
    protected $deathWithinRepository;
    protected $params;
    public function __construct(DeathWithinRepository $deathWithinRepository)
    {
        $this->deathWithinRepository = $deathWithinRepository;
    }
    public function withParams(DeathWithinDTO $params)
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
                                    ["wildcard" => ["death_within_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["death_within_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["death_within_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["death_within_code" => $this->params->keyword]]
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
            $data = $this->deathWithinRepository->applyJoins();
            $data = $this->deathWithinRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->deathWithinRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->deathWithinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->deathWithinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->deathWithinRepository->applyJoins();
        $data = $this->deathWithinRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->deathWithinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->deathWithinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->deathWithinRepository->applyJoins()
            ->where('his_death_within.id', $id);
        $data = $this->deathWithinRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->deathWithinName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->deathWithinName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->deathWithinName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->deathWithinName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }

    public function createDeathWithin($request)
    {
        try {
            $data = $this->deathWithinRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDeathWithinIndex($data, $this->params->deathWithinName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->deathWithinName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }

    public function updateDeathWithin($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->deathWithinRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->deathWithinRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDeathWithinIndex($data, $this->params->deathWithinName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->deathWithinName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }

    public function deleteDeathWithin($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->deathWithinRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->deathWithinRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->deathWithinName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->deathWithinName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }
}
