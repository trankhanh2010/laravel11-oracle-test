<?php

namespace App\Services\Model;

use App\DTOs\DocumentBookDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DocumentBook\InsertDocumentBookIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DocumentBookRepository;
use Illuminate\Support\Facades\Redis;

class DocumentBookService
{
    protected $documentBookRepository;
    protected $params;
    public function __construct(DocumentBookRepository $documentBookRepository)
    {
        $this->documentBookRepository = $documentBookRepository;
    }
    public function withParams(DocumentBookDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->documentBookRepository->applyJoins();
            $data = $this->documentBookRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->documentBookRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->documentBookRepository->applyTabFilter($data, $this->params->tab);
            $count = $data->count();
            $data = $this->documentBookRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->documentBookRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_book'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->documentBookRepository->applyJoins();
        $data = $this->documentBookRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->documentBookRepository->applyTabFilter($data, $this->params->tab);
        $count = $data->count();
        $data = $this->documentBookRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->documentBookRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->documentBookRepository->applyJoins()
            ->where('his_document_book.id', $id);
        $data = $this->documentBookRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_book'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_book'], $e);
        }
    }
}
