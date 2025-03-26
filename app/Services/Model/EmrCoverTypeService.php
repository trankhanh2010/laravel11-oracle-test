<?php

namespace App\Services\Model;

use App\DTOs\EmrCoverTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\EmrCoverType\InsertEmrCoverTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EmrCoverTypeRepository;

class EmrCoverTypeService 
{
    protected $emrCoverTypeRepository;
    protected $params;
    public function __construct(EmrCoverTypeRepository $emrCoverTypeRepository)
    {
        $this->emrCoverTypeRepository = $emrCoverTypeRepository;
    }
    public function withParams(EmrCoverTypeDTO $params)
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
                                    ["wildcard" => ["emr_cover_type_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["emr_cover_type_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["emr_cover_type_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["emr_cover_type_code" => $this->params->keyword]]
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
            $data = $this->emrCoverTypeRepository->applyJoins();
            $data = $this->emrCoverTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->emrCoverTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->emrCoverTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->emrCoverTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_cover_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->emrCoverTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->emrCoverTypeRepository->applyJoins();
                $data = $this->emrCoverTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->emrCoverTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->emrCoverTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_cover_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->emrCoverTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->emrCoverTypeRepository->applyJoins()
                    ->where('his_emr_cover_type.id', $id);
                $data = $this->emrCoverTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_cover_type'], $e);
        }
    }

    public function createEmrCoverType($request)
    {
        try {
            $data = $this->emrCoverTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmrCoverTypeIndex($data, $this->params->emrCoverTypeName));
             // Gọi event để xóa cache
             event(new DeleteCache($this->params->emrCoverTypeName));           
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_cover_type'], $e);
        }
    }

    public function updateEmrCoverType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emrCoverTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emrCoverTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmrCoverTypeIndex($data, $this->params->emrCoverTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emrCoverTypeName));            
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_cover_type'], $e);
        }
    }

    public function deleteEmrCoverType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emrCoverTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emrCoverTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->emrCoverTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emrCoverTypeName));            
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emr_cover_type'], $e);
        }
    }
}
