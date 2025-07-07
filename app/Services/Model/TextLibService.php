<?php

namespace App\Services\Model;

use App\DTOs\TextLibDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TextLib\InsertTextLibIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TextLibRepository;

class TextLibService
{
    protected $textLibRepository;
    protected $params;
    public function __construct(TextLibRepository $textLibRepository)
    {
        $this->textLibRepository = $textLibRepository;
    }
    public function withParams(TextLibDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->textLibRepository->applyJoins();
            $data = $this->textLibRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->textLibRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->textLibRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->textLibRepository->applyTabFilter($data, $this->params->tab, $this->params->currentLoginname, $this->params->currentDepartmentId);
            $data = $this->textLibRepository->applyHashTagsFilter($data, $this->params->hashTags);
            $count = $data->count();
            $data = $this->textLibRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->textLibRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['text_lib'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->textLibRepository->applyJoins();
        $data = $this->textLibRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->textLibRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->textLibRepository->applyTabFilter($data, $this->params->tab, $this->params->currentLoginname, $this->params->currentDepartmentId);
        $data = $this->textLibRepository->applyHashTagsFilter($data, $this->params->hashTags);
        $count = $data->count();
        $data = $this->textLibRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->textLibRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->textLibRepository->applyJoins()
        ->where('his_text_lib.id', $id);
    $data = $this->textLibRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->textLibRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['text_lib'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['text_lib'], $e);
        }
    }
}
