<?php

namespace App\Services\Model;

use App\DTOs\EmrFormDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\EmrForm\InsertEmrFormIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EmrFormRepository;
use Illuminate\Support\Facades\Redis;

class EmrFormService
{
    protected $emrFormRepository;
    protected $params;
    public function __construct(EmrFormRepository $emrFormRepository)
    {
        $this->emrFormRepository = $emrFormRepository;
    }
    public function withParams(EmrFormDTO $params)
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
                                    ["wildcard" => ["emr_form_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["emr_form_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["emr_form_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["emr_form_code" => $this->params->keyword]]
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
            $data = $this->emrFormRepository->applyJoins();
            $data = $this->emrFormRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->emrFormRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->emrFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->emrFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_form'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->emrFormRepository->applyJoins();
        $data = $this->emrFormRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->emrFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->emrFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->emrFormRepository->applyJoins()
            ->where('his_emr_form.id', $id);
        $data = $this->emrFormRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->emrFormName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->emrFormName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_form'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->emrFormName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->emrFormName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_form'], $e);
        }
    }

    public function createEmrForm($request)
    {
        try {
            $data = $this->emrFormRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmrFormIndex($data, $this->params->emrFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emrFormName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_form'], $e);
        }
    }

    public function updateEmrForm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emrFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emrFormRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmrFormIndex($data, $this->params->emrFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emrFormName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_form'], $e);
        }
    }

    public function deleteEmrForm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emrFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emrFormRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->emrFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emrFormName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_form'], $e);
        }
    }
}
