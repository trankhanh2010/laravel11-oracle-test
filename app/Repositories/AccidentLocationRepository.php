<?php 
namespace App\Repositories;

use App\Models\HIS\AccidentLocation;
use Illuminate\Support\Facades\DB;

class AccidentLocationRepository
{
    protected $accidentLocation;
    public function __construct(AccidentLocation $accidentLocation)
    {
        $this->accidentLocation = $accidentLocation;
    }

    public function applyJoins()
    {
        return $this->accidentLocation
            ->select(
                'his_accident_location.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_location.accident_location_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_accident_location.accident_location_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_location.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_accident_location.' . $key, $item);
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
        return $this->accidentLocation->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->accidentLocation::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'accident_location_code' => $request->accident_location_code,
            'accident_location_name' => $request->accident_location_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'accident_location_code' => $request->accident_location_code,
            'accident_location_name' => $request->accident_location_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_accident_location')
        ->select(
            'his_accident_location.*'
        );
        if($id != null){
            $data = $data->where('his_accident_location.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
