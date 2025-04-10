<?php

namespace App\Services\Model;

use App\DTOs\PtttCatastropheDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PtttCatastrophe\InsertPtttCatastropheIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PtttCatastropheRepository;
use Illuminate\Support\Facades\Redis;

class PtttCatastropheService
{
    protected $ptttCatastropheRepository;
    protected $params;
    public function __construct(PtttCatastropheRepository $ptttCatastropheRepository)
    {
        $this->ptttCatastropheRepository = $ptttCatastropheRepository;
    }
    public function withParams(PtttCatastropheDTO $params)
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
                                    ["wildcard" => ["pttt_catastrophe_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["pttt_catastrophe_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["pttt_catastrophe_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["pttt_catastrophe_code" => $this->params->keyword]]
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
            $data = $this->ptttCatastropheRepository->applyJoins();
            $data = $this->ptttCatastropheRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ptttCatastropheRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ptttCatastropheRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ptttCatastropheRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_catastrophe'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->ptttCatastropheRepository->applyJoins();
        $data = $this->ptttCatastropheRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->ptttCatastropheRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->ptttCatastropheRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->ptttCatastropheRepository->applyJoins()
            ->where('his_pttt_catastrophe.id', $id);
        $data = $this->ptttCatastropheRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->ptttCatastropheName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ptttCatastropheName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_catastrophe'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->ptttCatastropheName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ptttCatastropheName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_catastrophe'], $e);
        }
    }

    public function createPtttCatastrophe($request)
    {
        try {
            $data = $this->ptttCatastropheRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPtttCatastropheIndex($data, $this->params->ptttCatastropheName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttCatastropheName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_catastrophe'], $e);
        }
    }

    public function updatePtttCatastrophe($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttCatastropheRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttCatastropheRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPtttCatastropheIndex($data, $this->params->ptttCatastropheName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttCatastropheName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_catastrophe'], $e);
        }
    }

    public function deletePtttCatastrophe($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttCatastropheRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttCatastropheRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ptttCatastropheName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttCatastropheName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_catastrophe'], $e);
        }
    }
}
