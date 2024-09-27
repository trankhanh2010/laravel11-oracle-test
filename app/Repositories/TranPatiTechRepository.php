<?php 
namespace App\Repositories;

use App\Models\HIS\TranPatiTech;
use Illuminate\Support\Facades\DB;

class TranPatiTechRepository
{
    protected $tranPatiTech;
    public function __construct(TranPatiTech $tranPatiTech)
    {
        $this->tranPatiTech = $tranPatiTech;
    }

    public function applyJoins()
    {
        return $this->tranPatiTech
            ->select(
                'his_tran_pati_tech.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_tran_pati_tech.tran_pati_tech_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_tran_pati_tech.tran_pati_tech_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_tran_pati_tech.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_tran_pati_tech.' . $key, $item);
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
        return $this->tranPatiTech->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->tranPatiTech::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'tran_pati_tech_code' => $request->tran_pati_tech_code,
            'tran_pati_tech_name' => $request->tran_pati_tech_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'tran_pati_tech_code' => $request->tran_pati_tech_code,
            'tran_pati_tech_name' => $request->tran_pati_tech_name,
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
            $data = $data->where('his_tran_pati_tech.id','=', $id)->first();
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