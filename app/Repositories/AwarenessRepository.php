<?php 
namespace App\Repositories;

use App\Models\HIS\Awareness;
use Illuminate\Support\Facades\DB;

class AwarenessRepository
{
    protected $awareness;
    public function __construct(Awareness $awareness)
    {
        $this->awareness = $awareness;
    }

    public function applyJoins()
    {
        return $this->awareness
            ->select(
                'his_awareness.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_awareness.awareness_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_awareness.awareness_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_awareness.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_awareness.' . $key, $item);
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
        return $this->awareness->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->awareness::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'awareness_code' => $request->awareness_code,
            'awareness_name' => $request->awareness_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'awareness_code' => $request->awareness_code,
            'awareness_name' => $request->awareness_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_awareness')
        ->select(
            'his_awareness.*'
        );
        if($id != null){
            $data = $data->where('his_awareness.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}