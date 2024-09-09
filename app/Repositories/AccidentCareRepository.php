<?php 
namespace App\Repositories;

use App\Models\HIS\AccidentCare;
use Illuminate\Support\Facades\DB;

class AccidentCareRepository
{
    protected $accidentCare;
    public function __construct(AccidentCare $accidentCare)
    {
        $this->accidentCare = $accidentCare;
    }

    public function applyJoins()
    {
        return $this->accidentCare
            ->select(
                'his_accident_care.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_care.accident_care_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_accident_care.accident_care_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_care.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_accident_care.' . $key, $item);
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
        return $this->accidentCare->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->accidentCare::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'accident_care_code' => $request->accident_care_code,
            'accident_care_name' => $request->accident_care_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'accident_care_code' => $request->accident_care_code,
            'accident_care_name' => $request->accident_care_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_accident_care')
        ->select(
            'his_accident_care.*'
        );
        if($id != null){
            $data = $data->where('his_accident_care.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
