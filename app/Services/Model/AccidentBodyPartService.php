<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AccidentBodyPartRepository;

class AccidentBodyPartService extends BaseApiCacheController
{
    protected $accidentBodyPartRepository;
    protected $request;
    public function __construct(Request $request, AccidentBodyPartRepository $accidentBodyPartRepository)
    {
        parent::__construct($request);
        $this->accidentBodyPartRepository = $accidentBodyPartRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->accidentBodyPartRepository->applyJoins();
            $data = $this->accidentBodyPartRepository->applyKeywordFilter($data, $keyword);
            $data = $this->accidentBodyPartRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->accidentBodyPartRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->accidentBodyPartRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }
    public function handleDataBaseGetAll($accidentBodyPartName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($accidentBodyPartName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->accidentBodyPartRepository->applyJoins();
                $data = $this->accidentBodyPartRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->accidentBodyPartRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->accidentBodyPartRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }
    public function handleDataBaseGetWithId($accidentBodyPartName, $id, $isActive)
    {
        try {
            $data = Cache::remember($accidentBodyPartName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->accidentBodyPartRepository->applyJoins()
                    ->where('his_accident_body_part.id', $id);
                $data = $this->accidentBodyPartRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    public function createAccidentBodyPart($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->accidentBodyPartRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->accidentBodyPartName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentBodyPartIndex($data, $this->accidentBodyPartName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    public function updateAccidentBodyPart($accidentBodyPartName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentBodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentBodyPartRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentBodyPartName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentBodyPartIndex($data, $accidentBodyPartName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }

    public function deleteAccidentBodyPart($accidentBodyPartName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentBodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentBodyPartRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentBodyPartName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $accidentBodyPartName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_body_part'], $e);
        }
    }
}
