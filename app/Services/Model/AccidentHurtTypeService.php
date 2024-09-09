<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AccidentHurtTypeRepository;

class AccidentHurtTypeService extends BaseApiCacheController
{
    protected $accidentHurtTypeRepository;
    protected $request;
    public function __construct(Request $request, AccidentHurtTypeRepository $accidentHurtTypeRepository)
    {
        parent::__construct($request);
        $this->accidentHurtTypeRepository = $accidentHurtTypeRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->accidentHurtTypeRepository->applyJoins();
            $data = $this->accidentHurtTypeRepository->applyKeywordFilter($data, $keyword);
            $data = $this->accidentHurtTypeRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->accidentHurtTypeRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->accidentHurtTypeRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }
    public function handleDataBaseGetAll($accidentHurtTypeName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($accidentHurtTypeName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->accidentHurtTypeRepository->applyJoins();
                $data = $this->accidentHurtTypeRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->accidentHurtTypeRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->accidentHurtTypeRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($accidentHurtTypeName, $id, $isActive)
    {
        try {
            $data = Cache::remember($accidentHurtTypeName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->accidentHurtTypeRepository->applyJoins()
                    ->where('his_accident_hurt_type.id', $id);
                $data = $this->accidentHurtTypeRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }

    public function createAccidentHurtType($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->accidentHurtTypeRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->accidentHurtTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentHurtTypeIndex($data, $this->accidentHurtTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }

    public function updateAccidentHurtType($accidentHurtTypeName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentHurtTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentHurtTypeRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentHurtTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertAccidentHurtTypeIndex($data, $accidentHurtTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }

    public function deleteAccidentHurtType($accidentHurtTypeName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->accidentHurtTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->accidentHurtTypeRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($accidentHurtTypeName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $accidentHurtTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['accident_hurt_type'], $e);
        }
    }
}
