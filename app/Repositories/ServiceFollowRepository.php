<?php 
namespace App\Repositories;

use App\Models\HIS\ServiceFollow;
use Illuminate\Support\Facades\DB;

class ServiceFollowRepository
{
    protected $serviceFollow;
    public function __construct(ServiceFollow $serviceFollow)
    {
        $this->serviceFollow = $serviceFollow;
    }

    public function applyJoins()
    {
        return $this->serviceFollow
        ->leftJoin('his_service as service', 'service.id', '=', 'his_service_follow.service_id')
        ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
        ->leftJoin('his_service as service_follow', 'service_follow.id', '=', 'his_service_follow.follow_id')
        ->leftJoin('his_service_type as service_follow_type', 'service_type.id', '=', 'service_follow.service_type_id')

        ->select(
            'his_service_follow.*',
            'service.service_name',
            'service.service_code',
            'service_type.service_type_name',
            'service_type.service_type_code',
            'service_follow.service_name as service_follow_name',
            'service_follow.service_code as service_follow_code',
            'service_follow_type.service_type_name as service_follow_type_name',
            'service_follow_type.service_type_code as service_follow_type_code',
        );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('service.service_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_follow.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_name', 'service_code'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['service_type_name', 'service_type_code'])) {
                        $query->orderBy('service_type.' . $key, $item);
                    }
                    if (in_array($key, ['service_follow_name', 'service_follow_code'])) {
                        $query->orderBy('service_follow.' . $key, $item);
                    }
                    if (in_array($key, ['service_follow_type_name', 'service_follow_type_code'])) {
                        $query->orderBy('service_follow_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_follow.' . $key, $item);
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
        return $this->serviceFollow->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceFollow::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_id' => $request->service_id,
            'follow_id' => $request->follow_id,
            'amount' => $request->amount,
            'conditioned_amount' => $request->conditioned_amount,
            'treatment_type_ids' => $request->treatment_type_ids,
            'is_expend' => $request->is_expend,
            'add_if_not_assigned' => $request->add_if_not_assigned,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'service_id' => $request->service_id,
            'follow_id' => $request->follow_id,
            'amount' => $request->amount,
            'conditioned_amount' => $request->conditioned_amount,
            'is_expend' => $request->is_expend,
            'add_if_not_assigned' => $request->add_if_not_assigned,
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
            $data = $data->where('his_service_follow.id','=', $id)->first();
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