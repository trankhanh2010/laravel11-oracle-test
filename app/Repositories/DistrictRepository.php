<?php 
namespace App\Repositories;

use App\Models\SDA\District;
use Illuminate\Support\Facades\DB;

class DistrictRepository
{
    protected $district;
    public function __construct(District $district)
    {
        $this->district = $district;
    }

    public function applyJoins()
    {
        return $this->district
            ->leftJoin('sda_province as province', 'province.id', '=', 'sda_district.province_id')
            ->select(
                'sda_district.*',
                'province.province_name',
                'province.province_code',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_sda')->raw('sda_district.district_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_sda')->raw('sda_district.district_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_sda')->raw('sda_district.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['province_code', 'province_name'])) {
                        $query->orderBy('province.' . $key, $item);
                    }
                } else {
                    $query->orderBy('sda_district.' . $key, $item);
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
        return $this->district->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->district::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'province_id' => $request->province_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'province_id' => $request->province_id,
            'is_active' => $request->is_active,
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
            $data = $data->where('sda_district.id','=', $id)->first();
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