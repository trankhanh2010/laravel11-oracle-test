<?php 
namespace App\Repositories;

use App\Models\SDA\Province;
use Illuminate\Support\Facades\DB;

class ProvinceRepository
{
    protected $province;
    public function __construct(Province $province)
    {
        $this->province = $province;
    }

    public function applyJoins()
    {
        return $this->province
            ->leftJoin('sda_national as national', 'national.id', '=', 'sda_province.national_id')
            ->select(
                'sda_province.*',
                'national.national_name',
                'national.national_code',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_sda')->raw('sda_province.province_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_sda')->raw('sda_province.province_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_sda')->raw('sda_province.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['national_code', 'national_name'])) {
                        $query->orderBy('national.' . $key, $item);
                    }
                } else {
                    $query->orderBy('sda_province.' . $key, $item);
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
        return $this->province->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->province::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'search_code' => $request->search_code,
            'national_id' => $request->national_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'search_code' => $request->search_code,
            'national_id' => $request->national_id,
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
            $data = $data->where('sda_province.id','=', $id)->first();
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