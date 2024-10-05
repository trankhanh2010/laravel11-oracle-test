<?php 
namespace App\Repositories;

use App\Models\HIS\StorageCondition;
use Illuminate\Support\Facades\DB;

class StorageConditionRepository
{
    protected $storageCondition;
    public function __construct(StorageCondition $storageCondition)
    {
        $this->storageCondition = $storageCondition;
    }

    public function applyJoins()
    {
        return $this->storageCondition
            ->select(
                'his_storage_condition.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_storage_condition.storage_condition_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_storage_condition.storage_condition_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_storage_condition.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_storage_condition.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->storageCondition->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->storageCondition::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'storage_condition_code' => $request->storage_condition_code,
            'storage_condition_name' => $request->storage_condition_name,
            'from_temperature'  => $request->from_temperature,       
            'to_temperature'  => $request->to_temperature,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'storage_condition_code' => $request->storage_condition_code,
            'storage_condition_name' => $request->storage_condition_name,
            'from_temperature'  => $request->from_temperature,       
            'to_temperature'  => $request->to_temperature,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_storage_condition.id','=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes(); 
            })->toArray(); 
        }
        return $data;
    }
}