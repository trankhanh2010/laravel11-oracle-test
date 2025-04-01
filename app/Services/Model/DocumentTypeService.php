<?php

namespace App\Services\Model;

use App\DTOs\DocumentTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DocumentType\InsertDocumentTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DocumentTypeRepository;
use Illuminate\Support\Facades\Redis;

class DocumentTypeService
{
    protected $documentTypeRepository;
    protected $params;
    public function __construct(DocumentTypeRepository $documentTypeRepository)
    {
        $this->documentTypeRepository = $documentTypeRepository;
    }
    public function withParams(DocumentTypeDTO $params)
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
                                    ["wildcard" => ["document_type_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["document_type_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["document_type_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["document_type_code" => $this->params->keyword]]
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
            $data = $this->documentTypeRepository->applyJoins();
            $data = $this->documentTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->documentTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->documentTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->documentTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->documentTypeRepository->applyJoins();
        $data = $this->documentTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->documentTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->documentTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->documentTypeRepository->applyJoins()
            ->where('emr_document_type.id', $id);
        $data = $this->documentTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->documentTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->documentTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->documentTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->documentTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_type'], $e);
        }
    }

    public function createDocumentType($request)
    {
        try {
            $data = $this->documentTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDocumentTypeIndex($data, $this->params->documentTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->documentTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_type'], $e);
        }
    }

    public function updateDocumentType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->documentTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->documentTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDocumentTypeIndex($data, $this->params->documentTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->documentTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_type'], $e);
        }
    }

    public function deleteDocumentType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->documentTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->documentTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->documentTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->documentTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_type'], $e);
        }
    }
}
