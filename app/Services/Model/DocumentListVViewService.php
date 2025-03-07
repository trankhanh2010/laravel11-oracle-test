<?php

namespace App\Services\Model;

use App\DTOs\DocumentListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DocumentListVView\InsertDocumentListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DocumentListVViewRepository;

class DocumentListVViewService
{
    protected $documentListVViewRepository;
    protected $params;
    public function __construct(DocumentListVViewRepository $documentListVViewRepository)
    {
        $this->documentListVViewRepository = $documentListVViewRepository;
    }
    public function withParams(DocumentListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->documentListVViewRepository->applyJoins();
            $data = $this->documentListVViewRepository->applyWithParam($data);
            $data = $this->documentListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->documentListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->documentListVViewRepository->applyDocumentTypeIdFilter($data, $this->params->documentTypeId);
            $data = $this->documentListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $count = $data->count();
            $data = $this->documentListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->documentListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->documentListVViewRepository->applyJoins();
            $data = $this->documentListVViewRepository->applyWithParam($data);
            $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->documentListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->documentListVViewRepository->applyDocumentTypeIdFilter($data, $this->params->documentTypeId);
            $data = $this->documentListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $count = $data->count();
            $data = $this->documentListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->documentListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->documentListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }

    // public function createDocumentListVView($request)
    // {
    //     try {
    //         $data = $this->documentListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->documentListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDocumentListVViewIndex($data, $this->params->documentListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
    //     }
    // }

    // public function updateDocumentListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->documentListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->documentListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->documentListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDocumentListVViewIndex($data, $this->params->documentListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
    //     }
    // }

    // public function deleteDocumentListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->documentListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->documentListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->documentListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->documentListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
    //     }
    // }
}
