<?php

namespace App\Services\Model;

use App\DTOs\ExecuteRoleUserDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExecuteRoleUser\InsertExecuteRoleUserIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\EmployeeRepository;
use App\Repositories\ExecuteRoleRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExecuteRoleUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ExecuteRoleUserService
{
    protected $executeRoleUserRepository;
    protected $employeeRepository;
    protected $executeRoleRepository;
    protected $params;
    public function __construct(ExecuteRoleUserRepository $executeRoleUserRepository, EmployeeRepository $employeeRepository, ExecuteRoleRepository $executeRoleRepository)
    {
        $this->executeRoleUserRepository = $executeRoleUserRepository;
        $this->employeeRepository = $employeeRepository;
        $this->executeRoleRepository = $executeRoleRepository;
    }
    public function withParams(ExecuteRoleUserDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->executeRoleUserRepository->applyJoins();
            $data = $this->executeRoleUserRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->executeRoleUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->executeRoleUserRepository->applyLoginnameFilter($data, $this->params->loginname);
            $data = $this->executeRoleUserRepository->applyExecuteRoleIdFilter($data, executeRoleId: $this->params->executeRoleId);
            $count = $data->count();
            $data = $this->executeRoleUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->executeRoleUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->executeRoleUserName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->executeRoleUserName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->executeRoleUserRepository->applyJoins();
                $data = $this->executeRoleUserRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->executeRoleUserRepository->applyLoginnameFilter($data, $this->params->loginname);
                $data = $this->executeRoleUserRepository->applyExecuteRoleIdFilter($data, executeRoleId: $this->params->executeRoleId);
                $count = $data->count();
                $data = $this->executeRoleUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->executeRoleUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->executeRoleUserName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->executeRoleUserName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->executeRoleUserRepository->applyJoins()
                    ->where('his_execute_role_user.id', $id);
                $data = $this->executeRoleUserRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'app_creator' => $this->params->appCreator,
            'app_modifier' => $this->params->appModifier,
        ];
    }
    public function createExecuteRoleUser($request)
    {
        try {
            if ($request->execute_role_id != null) {
                $id = $request->execute_role_id;
                $data = $this->executeRoleRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->loginnames !== null) {
                        $loginnames_arr = explode(',', $request->loginnames);
                        foreach ($loginnames_arr as $key => $item) {
                            $loginnames_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->employees()->sync($loginnames_arr_data);
                    } else {
                        $deleteIds = $this->executeRoleUserRepository->deleteByExecuteRoleId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->executeRoleUserName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->executeRoleUserRepository->getByExecuteRoleIdAndLoginnames($id, $loginnames_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertExecuteRoleUserIndex($item, $this->params->executeRoleUserName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->loginname != null) {
                $id = $request->loginname;
                $data = $this->employeeRepository->getByLoginname($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->execute_role_ids !== null) {
                        $execute_role_ids_arr = explode(',', $request->execute_role_ids);
                        foreach ($execute_role_ids_arr as $key => $item) {
                            $execute_role_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->execute_roles()->sync($execute_role_ids_arr_data);
                    } else {
                        $deleteIds = $this->executeRoleUserRepository->deleteByLoginname($request->loginname);
                        event(new DeleteIndex($deleteIds, $this->params->executeRoleUserName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->executeRoleUserRepository->getByLoginnameAndExecuteRoleIds($id, $execute_role_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertExecuteRoleUserIndex($item, $this->params->executeRoleUserName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->executeRoleUserName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
}
