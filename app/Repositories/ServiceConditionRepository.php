<?php 
namespace App\Repositories;

use App\Models\HIS\ServiceCondition;
use Illuminate\Support\Facades\DB;

class ServiceConditionRepository
{
    protected $serviceCondition;
    public function __construct(ServiceCondition $serviceCondition)
    {
        $this->serviceCondition = $serviceCondition;
    }

    public function applyJoins()
    {
        return $this->serviceCondition
            ->select(
                'his_service_condition.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_condition.service_condition_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service_condition.service_condition_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_condition.is_active'), $isActive);
        }
        return $query;
    }
    public function applyServiceIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_condition.service_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_service_condition.' . $key, $item);
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
        return $this->serviceCondition->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceCondition::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_condition_code' => $request->service_condition_code,
            'service_condition_name' => $request->service_condition_name,
            'hein_ratio' => $request->hein_ratio,
            'hein_price' => $request->hein_price,
            'service_id' => $request->service_id,

        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'service_condition_code' => $request->service_condition_code,
            'service_condition_name' => $request->service_condition_name,
            'hein_ratio' => $request->hein_ratio,
            'hein_price' => $request->hein_price,
            'service_id' => $request->service_id,
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
            $data = $data->where('his_service_condition.id','=', $id)->first();
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