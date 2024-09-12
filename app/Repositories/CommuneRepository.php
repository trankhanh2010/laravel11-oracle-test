<?php 
namespace App\Repositories;

use App\Models\SDA\Commune;
use Illuminate\Support\Facades\DB;

class CommuneRepository
{
    protected $commune;
    public function __construct(Commune $commune)
    {
        $this->commune = $commune;
    }

    public function applyJoins()
    {
        return $this->commune
            ->leftJoin('sda_district as district', 'district.id', '=', 'sda_commune.district_id')
            ->select(
                'sda_commune.*',
                'district.district_name',
                'district.district_code',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_sda')->raw('sda_commune.commune_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_sda')->raw('sda_commune.commune_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_sda')->raw('sda_commune.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['district_code', 'district_name'])) {
                        $query->orderBy('district.' . $key, $item);
                    }
                } else {
                    $query->orderBy('sda_commune.' . $key, $item);
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
        return $this->commune->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->commune::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'district_id' => $request->district_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'district_id' => $request->district_id,
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
            $data = $data->where('sda_commune.id','=', $id)->first();
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