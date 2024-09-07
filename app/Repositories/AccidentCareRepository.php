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
    public function applyIsActiveFilter($query, $is_active)
    {
        if ($is_active !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_care.is_active'), $is_active);
        }

        return $query;
    }
    public function applyOrdering($query, $order_by, $order_by_join)
    {
        if ($order_by != null) {
            foreach ($order_by as $key => $item) {
                if (in_array($key, $order_by_join)) {

                } else {
                    $query->orderBy('his_accident_care.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $get_all, $start, $limit)
    {
        if ($get_all) {
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
    public function create($request, $time, $app_creator, $app_modifier){
        $data = $this->accidentCare::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $app_creator,
            'app_modifier' => $app_modifier,
            'is_active' => 1,
            'is_delete' => 0,
            'accident_care_code' => $request->accident_care_code,
            'accident_care_name' => $request->accident_care_name,
        ]);
        return $data;
    }

    public function update($request, $data, $time, $app_modifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $app_modifier,
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
}
