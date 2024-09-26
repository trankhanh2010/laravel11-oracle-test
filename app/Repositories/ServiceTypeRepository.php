<?php

namespace App\Repositories;

use App\Models\HIS\ServiceType;
use Illuminate\Support\Facades\DB;

class ServiceTypeRepository
{
    protected $serviceType;
    public function __construct(ServiceType $serviceType)
    {
        $this->serviceType = $serviceType;
    }

    public function applyJoins()
    {
        return $this->serviceType
            ->leftJoin('his_exe_service_module as exe_service_module', 'exe_service_module.id', '=', 'his_service_type.exe_service_module_id')
            ->select(
                'his_service_type.*',
                'exe_service_module.exe_service_module_name',
                'exe_service_module.module_link'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_type.service_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service_type.service_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['exe_service_module_name', 'module_link'])) {
                        $query->orderBy('exe_service_module.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_type.' . $key, $item);
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
        return $this->serviceType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->serviceType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_unit_code' => $request->service_unit_code,
            'service_unit_name' => $request->service_unit_name,
            'service_unit_symbol' => $request->service_unit_symbol,
            'medicine_num_order' => $request->medicine_num_order,
            'material_num_order' => $request->material_num_order,
            'convert_id' => $request->convert_id,
            'convert_ratio' => $request->convert_ratio,
            'is_primary' => $request->is_primary,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'service_unit_code' => $request->service_unit_code,
            'service_unit_name' => $request->service_unit_name,
            'service_unit_symbol' => $request->service_unit_symbol,
            'medicine_num_order' => $request->medicine_num_order,
            'material_num_order' => $request->material_num_order,
            'convert_id' => $request->convert_id,
            'convert_ratio' => $request->convert_ratio,
            'is_primary' => $request->is_primary,

            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_service_type.id', '=', $id)->first();
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
