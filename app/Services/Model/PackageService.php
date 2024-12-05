<?php

namespace App\Services\Model;

use App\DTOs\PackageDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Package\InsertPackageIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PackageRepository;

class PackageService 
{
    protected $packageRepository;
    protected $params;
    public function __construct(PackageRepository $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }
    public function withParams(PackageDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->packageRepository->applyJoins();
            $data = $this->packageRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->packageRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->packageRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->packageRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->packageName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->packageRepository->applyJoins();
                $data = $this->packageRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->packageRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->packageRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->packageName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->packageRepository->applyJoins()
                    ->where('his_package.id', $id);
                $data = $this->packageRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
    public function createPackage($request)
    {
        try {
            $data = $this->packageRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPackageIndex($data, $this->params->packageName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packageName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }

    public function updatePackage($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packageRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packageRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPackageIndex($data, $this->params->packageName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packageName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }

    public function deletePackage($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packageRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packageRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->packageName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packageName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
}
