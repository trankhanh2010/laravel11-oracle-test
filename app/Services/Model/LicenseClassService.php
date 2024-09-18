<?php

namespace App\Services\Model;

use App\DTOs\LicenseClassDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\LicenseClass\InsertLicenseClassIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\LicenseClassRepository;

class LicenseClassService 
{
    protected $licenseClassRepository;
    protected $params;
    public function __construct(LicenseClassRepository $licenseClassRepository)
    {
        $this->licenseClassRepository = $licenseClassRepository;
    }
    public function withParams(LicenseClassDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->licenseClassRepository->applyJoins();
            $data = $this->licenseClassRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->licenseClassRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->licenseClassRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->licenseClassRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->licenseClassName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->licenseClassRepository->applyJoins();
                $data = $this->licenseClassRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->licenseClassRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->licenseClassRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->licenseClassName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->licenseClassRepository->applyJoins()
                    ->where('his_license_class.id', $id);
                $data = $this->licenseClassRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }

    public function createLicenseClass($request)
    {
        try {
            $data = $this->licenseClassRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->licenseClassName));
            // Gọi event để thêm index vào elastic
            event(new InsertLicenseClassIndex($data, $this->params->licenseClassName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }

    public function updateLicenseClass($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->licenseClassRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->licenseClassRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->licenseClassName));
            // Gọi event để thêm index vào elastic
            event(new InsertLicenseClassIndex($data, $this->params->licenseClassName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }

    public function deleteLicenseClass($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->licenseClassRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->licenseClassRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->licenseClassName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->licenseClassName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }
}
