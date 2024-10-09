<?php

namespace App\Services\Model;

use App\DTOs\DebateDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Debate\InsertDebateIndex;
use App\Events\Elastic\DeleteIndex;
use App\Models\HIS\Debate;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateRepository;

class DebateService
{
    protected $debateRepository;
    protected $params;
    protected $debate;
    public function __construct(DebateRepository $debateRepository, Debate $debate)
    {
        $this->debateRepository = $debateRepository;
        $this->debate = $debate;
    }
    public function withParams(DebateDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDebateDataBaseSearch()
    {
        try {
            $data = $this->debateRepository->debate($this->debate);
            $data = $this->debateRepository->selectDebate($data);
            $data = $this->debateRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->debateRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->debateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDebateDataBaseGetAll()
    {
        try {
            $data = $this->debateRepository->debate($this->debate);
            $data = $this->debateRepository->selectDebate($data);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->debateRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->debateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDebateDataBaseGetWithId($id)
    {
        try {
            $data = $this->debateRepository->debate($this->debate)
                ->where('his_debate.id', $id);
            $data = $this->debateRepository->selectDebate($data);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDebateViewDataBaseSearch()
    {
        try {
            $data = $this->debateRepository->debateView($this->debate);
            $data = $this->debateRepository->selectDebateView($data);
            $data = $this->debateRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->debateRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->debateRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $data = $this->debateRepository->applyDepartmentIdsFilter($data, $this->params->departmentIds);
            $count = $data->count();
            $data = $this->debateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDebateViewDataBaseGetAll()
    {
        try {
            $data = $this->debateRepository->debateView($this->debate);
            $data = $this->debateRepository->selectDebateView($data);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->debateRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->debateRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $data = $this->debateRepository->applyDepartmentIdsFilter($data, $this->params->departmentIds);
            $count = $data->count();
            $data = $this->debateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }
    public function handleDebateViewDataBaseGetWithId($id)
    {
        try {
            $data = $this->debateRepository->debateView($this->debate)
                ->where('his_debate.id', $id);
            $data = $this->debateRepository->selectDebateView($data);
            $data = $this->debateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
        }
    }

    // public function createDebate($request)
    // {
    //     try {
    //         $data = $this->debateRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateIndex($data, $this->params->debateName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
    //     }
    // }

    // public function updateDebate($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateIndex($data, $this->params->debateName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
    //     }
    // }

    // public function deleteDebate($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->debateName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate'], $e);
    //     }
    // }
}
