<?php 
namespace App\Repositories;

use App\Models\HIS\AgeType;
use Illuminate\Support\Facades\DB;

class AgeTypeRepository
{
    protected $ageType;
    public function __construct(AgeType $ageType)
    {
        $this->ageType = $ageType;
    }

    public function applyJoins()
    {
        return $this->ageType
            ->select(
                'his_age_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_age_type.age_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_age_type.age_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_age_type.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_age_type.' . $key, $item);
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
        return $this->ageType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->ageType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'age_type_code' => $request->age_type_code,
            'age_type_name' => $request->age_type_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'age_type_code' => $request->age_type_code,
            'age_type_name' => $request->age_type_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_age_type')
        ->select(
            'his_age_type.*'
        );
        if($id != null){
            $data = $data->where('his_age_type.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
