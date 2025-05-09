<?php

namespace App\Services\Model;

use App\DTOs\PtttConditionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PtttCondition\InsertPtttConditionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PtttConditionRepository;
use Illuminate\Support\Facades\Redis;

class PtttConditionService
{
    protected $ptttConditionRepository;
    protected $params;
    public function __construct(PtttConditionRepository $ptttConditionRepository)
    {
        $this->ptttConditionRepository = $ptttConditionRepository;
    }
    public function withParams(PtttConditionDTO $params)
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
                                    ["wildcard" => ["pttt_condition_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["pttt_condition_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["pttt_condition_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["pttt_condition_code" => $this->params->keyword]]
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
            $data = $this->ptttConditionRepository->applyJoins();
            $data = $this->ptttConditionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ptttConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ptttConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ptttConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_condition'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->ptttConditionRepository->applyJoins();
        $data = $this->ptttConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->ptttConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->ptttConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->ptttConditionRepository->applyJoins()
            ->where('his_pttt_condition.id', $id);
        $data = $this->ptttConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->ptttConditionName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ptttConditionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_condition'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->ptttConditionName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ptttConditionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_condition'], $e);
        }
    }

    public function createPtttCondition($request)
    {
        try {
            $data = $this->ptttConditionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPtttConditionIndex($data, $this->params->ptttConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttConditionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_condition'], $e);
        }
    }

    public function updatePtttCondition($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttConditionRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPtttConditionIndex($data, $this->params->ptttConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttConditionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_condition'], $e);
        }
    }

    public function deletePtttCondition($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttConditionRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ptttConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttConditionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_condition'], $e);
        }
    }
}
