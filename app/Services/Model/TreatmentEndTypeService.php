<?php

namespace App\Services\Model;

use App\DTOs\TreatmentEndTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentEndType\InsertTreatmentEndTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentEndTypeRepository;
use Illuminate\Support\Facades\Redis;

class TreatmentEndTypeService 
{
    protected $treatmentEndTypeRepository;
    protected $params;
    public function __construct(TreatmentEndTypeRepository $treatmentEndTypeRepository)
    {
        $this->treatmentEndTypeRepository = $treatmentEndTypeRepository;
    }
    public function withParams(TreatmentEndTypeDTO $params)
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
                                    ["wildcard" => ["treatment_end_type_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["treatment_end_type_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["treatment_end_type_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["treatment_end_type_code" => $this->params->keyword]]
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
            $data = $this->treatmentEndTypeRepository->applyJoins();
            $data = $this->treatmentEndTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentEndTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->treatmentEndTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentEndTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->treatmentEndTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->treatmentEndTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->treatmentEndTypeRepository->applyJoins();
                $data = $this->treatmentEndTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->treatmentEndTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->treatmentEndTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->treatmentEndTypeName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->treatmentEndTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->treatmentEndTypeRepository->applyJoins()
                    ->where('his_treatment_end_type.id', $id);
                $data = $this->treatmentEndTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type'], $e);
        }
    }

    public function createTreatmentEndType($request)
    {
        try {
            $data = $this->treatmentEndTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentEndTypeIndex($data, $this->params->treatmentEndTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentEndTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type'], $e);
        }
    }

    public function updateTreatmentEndType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentEndTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentEndTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentEndTypeIndex($data, $this->params->treatmentEndTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentEndTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type'], $e);
        }
    }

    public function deleteTreatmentEndType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentEndTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentEndTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->treatmentEndTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentEndTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type'], $e);
        }
    }
}
