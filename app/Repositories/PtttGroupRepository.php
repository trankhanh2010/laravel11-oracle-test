<?php 
namespace App\Repositories;

use App\Models\HIS\PtttGroup;
use Illuminate\Support\Facades\DB;

class PtttGroupRepository
{
    protected $ptttGroup;
    public function __construct(PtttGroup $ptttGroup)
    {
        $this->ptttGroup = $ptttGroup;
    }

    public function applyJoins()
    {
        return $this->ptttGroup
        ->with([
           'bed_services:service_name,service_code'
        ])
            ->select(
                'his_pttt_group.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_group.pttt_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_pttt_group.pttt_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_group.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_pttt_group.' . $key, $item);
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
        return $this->ptttGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $data = $this->ptttGroup::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'pttt_group_code' => $request->pttt_group_code,
            'pttt_group_name' => $request->pttt_group_name,
            'num_order' => $request->num_order,
            'remuneration' => $request->remuneration,
            'is_active' => $request->is_active,
        ]);
        if ($request->bed_service_type_ids !== null) {
            $dataToSync_bed_service_type_ids = [];
            $request->bed_service_type_ids = explode(',', $request->bed_service_type_ids);
            foreach ($request->bed_service_type_ids as $item) {
                $id = $item;
                $dataToSync_bed_service_type_ids[$id] = [];
                $dataToSync_bed_service_type_ids[$id]['create_time'] = now()->format('Ymdhis');
                $dataToSync_bed_service_type_ids[$id]['modify_time'] = now()->format('Ymdhis');
                $dataToSync_bed_service_type_ids[$id]['creator'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_bed_service_type_ids[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_bed_service_type_ids[$id]['app_creator'] = $appCreator;
                $dataToSync_bed_service_type_ids[$id]['app_modifier'] = $appModifier;
            }
            $data->bed_services()->sync($dataToSync_bed_service_type_ids);
        }
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'pttt_group_code' => $request->pttt_group_code,
            'pttt_group_name' => $request->pttt_group_name,
            'num_order' => $request->num_order,
            'remuneration' => $request->remuneration,
            'is_active' => $request->is_active,
        ];
        $data->fill($data_update);
        $data->save();
        if ($request->bed_service_type_ids !== null) {
            $dataToSync_bed_service_type_ids = [];
            $request->bed_service_type_ids = explode(',', $request->bed_service_type_ids);
            foreach ($request->bed_service_type_ids as $item) {
                $id = $item;
                $dataToSync_bed_service_type_ids[$id] = [];
                $dataToSync_bed_service_type_ids[$id]['modify_time'] = now()->format('Ymdhis');
                $dataToSync_bed_service_type_ids[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_bed_service_type_ids[$id]['app_modifier'] = $appModifier;
            }
            $data->bed_services()->sync($dataToSync_bed_service_type_ids);
        }
        else{
            $data->bed_services()->sync([]);
        }
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_pttt_group.id', '=', $id)->first();
            if ($data) {
                $data->toArray();
            }
        } else {
            $data = $data->get();
            $data->toArray();
        }
        return $data;
    }
}