<?php

namespace App\Services\Model;

use App\DTOs\TreatmentResultDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentResult\InsertTreatmentResultIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentResultRepository;
use Illuminate\Support\Facades\Redis;

class TreatmentResultService
{
    protected $treatmentResultRepository;
    protected $params;
    public function __construct(TreatmentResultRepository $treatmentResultRepository)
    {
        $this->treatmentResultRepository = $treatmentResultRepository;
    }
    public function withParams(TreatmentResultDTO $params)
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
                                    ["wildcard" => ["treatment_result_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["treatment_result_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["treatment_result_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["treatment_result_code" => $this->params->keyword]]
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
            $data = $this->treatmentResultRepository->applyJoins();
            $data = $this->treatmentResultRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentResultRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->treatmentResultRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentResultRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentResultRepository->applyJoins();
        $data = $this->treatmentResultRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->treatmentResultRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentResultRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentResultRepository->applyJoins()
            ->where('his_treatment_result.id', $id);
        $data = $this->treatmentResultRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->treatmentResultName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->treatmentResultName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->treatmentResultName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->treatmentResultName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }

    public function createTreatmentResult($request)
    {
        try {
            $data = $this->treatmentResultRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentResultIndex($data, $this->params->treatmentResultName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentResultName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }

    public function updateTreatmentResult($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentResultRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentResultRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentResultIndex($data, $this->params->treatmentResultName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentResultName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }

    public function deleteTreatmentResult($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentResultRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentResultRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->treatmentResultName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentResultName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_result'], $e);
        }
    }
}
