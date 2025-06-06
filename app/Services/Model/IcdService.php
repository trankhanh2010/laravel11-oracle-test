<?php

namespace App\Services\Model;

use App\DTOs\IcdDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Icd\InsertIcdIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\IcdRepository;
use Illuminate\Support\Facades\Redis;

class IcdService
{
    protected $icdRepository;
    protected $params;
    public function __construct(IcdRepository $icdRepository)
    {
        $this->icdRepository = $icdRepository;
    }
    public function withParams(IcdDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleCustomParamElasticSearch()
    {
        $data = null;
        if (in_array($this->params->tab, ['select', 'selectNguyenNhanNgoai'])) {
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
            if($this->params->tab == 'selectNguyenNhanNgoai'){
                $data['bool']['filter'][] = ["term" => ["is_cause" => 1]];
            }
        }

        return $data;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->icdRepository->applyJoins();
            $data = $this->icdRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->icdRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->icdRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->icdRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->icdRepository->applyJoins();
        $data = $this->icdRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->icdRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->icdRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->icdRepository->applyJoins()
            ->where('his_icd.id', $id);
        $data = $this->icdRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->icdName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->icdName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->icdName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->icdName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }

    public function createIcd($request)
    {
        try {
            $data = $this->icdRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertIcdIndex($data, $this->params->icdName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }

    public function updateIcd($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icdRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->icdRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertIcdIndex($data, $this->params->icdName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }

    public function deleteIcd($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icdRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->icdRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->icdName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }
}
