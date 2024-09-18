<?php 
namespace App\Repositories;

use App\Models\HIS\Manufacturer;
use Illuminate\Support\Facades\DB;

class ManufacturerRepository
{
    protected $manufacturer;
    public function __construct(Manufacturer $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    public function applyJoins()
    {
        return $this->manufacturer
            ->select(
                'his_manufacturer.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_manufacturer.manufacturer_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_manufacturer.manufacturer_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_manufacturer.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_manufacturer.' . $key, $item);
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
        return $this->manufacturer->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->manufacturer::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            
            'manufacturer_code' => $request->manufacturer_code,
            'manufacturer_name' => $request->manufacturer_name,
            'manufacturer_short_name' => $request->manufacturer_short_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
           
            'manufacturer_code' => $request->manufacturer_code,
            'manufacturer_name' => $request->manufacturer_name,
            'manufacturer_short_name' => $request->manufacturer_short_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,

            'is_active' => $request->is_active
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
            $data = $data->where('his_manufacturer.id','=', $id)->first();
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