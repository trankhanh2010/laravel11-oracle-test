<?php 
namespace App\Repositories;

use App\Models\HIS\AtcGroup;
use Illuminate\Support\Facades\DB;

class AtcGroupRepository
{
    protected $atcGroup;
    public function __construct(AtcGroup $atcGroup)
    {
        $this->atcGroup = $atcGroup;
    }

    public function applyJoins()
    {
        return $this->atcGroup
            ->select(
                'his_atc_group.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_atc_group.atc_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_atc_group.atc_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_atc_group.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_atc_group.' . $key, $item);
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
        return $this->atcGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->atcGroup::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'atc_group_code' => $request->atc_group_code,
            'atc_group_name' => $request->atc_group_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'atc_group_code' => $request->atc_group_code,
            'atc_group_name' => $request->atc_group_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_atc_group')
        ->select(
            'his_atc_group.*'
        );
        if($id != null){
            $data = $data->where('his_atc_group.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}