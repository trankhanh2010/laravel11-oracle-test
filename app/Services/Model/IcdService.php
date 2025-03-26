<?php

namespace App\Services\Model;

use App\DTOs\IcdDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Icd\InsertIcdIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\IcdRepository;

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
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->icdName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->icdRepository->applyJoins();
                $data = $this->icdRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->icdRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->icdRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->icdName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->icdRepository->applyJoins()
                    ->where('his_icd.id', $id);
                $data = $this->icdRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
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
