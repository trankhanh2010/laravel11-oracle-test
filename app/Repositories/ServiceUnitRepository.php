<?php 
namespace App\Repositories;

use App\Models\HIS\ServiceUnit;
use Illuminate\Support\Facades\DB;

class ServiceUnitRepository
{
    protected $serviceUnit;
    public function __construct(ServiceUnit $serviceUnit)
    {
        $this->serviceUnit = $serviceUnit;
    }

    public function applyJoins()
    {
        return $this->serviceUnit
        ->leftJoin('his_service_unit as convert', 'convert.id', '=', 'his_service_unit.convert_id')
            ->select(
                'his_service_unit.*',
                'convert.service_unit_code as convert_service_unit_code',
                'convert.service_unit_name as convert_service_unit_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_unit.service_unit_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service_unit.service_unit_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_unit.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['convert_service_unit_code', 'convert_service_unit_name'])) {
                        $query->orderBy('convert.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_unit.' . $key, $item);
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
        return $this->serviceUnit->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceUnit::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'service_unit_code' => $request->service_unit_code,
            'service_unit_name' => $request->service_unit_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'service_unit_code' => $request->service_unit_code,
            'service_unit_name' => $request->service_unit_name,
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
            $data = $data->where('his_service_unit.id','=', $id)->first();
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