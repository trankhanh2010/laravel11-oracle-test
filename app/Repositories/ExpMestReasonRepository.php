<?php 
namespace App\Repositories;

use App\Models\HIS\ExpMestReason;
use Illuminate\Support\Facades\DB;

class ExpMestReasonRepository
{
    protected $expMestReason;
    public function __construct(ExpMestReason $expMestReason)
    {
        $this->expMestReason = $expMestReason;
    }

    public function applyJoins()
    {
        return $this->expMestReason
            ->select(
                'his_exp_mest_reason.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_reason.exp_mest_reason_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_exp_mest_reason.exp_mest_reason_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_reason.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_exp_mest_reason.' . $key, $item);
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
        return $this->expMestReason->find($id);
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_exp_mest_reason.id','=', $id)->first();
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
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->expMestReason::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'exp_mest_reason_code' => $request->exp_mest_reason_code,
            'exp_mest_reason_name' => $request->exp_mest_reason_name,
            'is_depa' => $request->is_depa,
            'is_odd' => $request->is_odd,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'exp_mest_reason_code' => $request->exp_mest_reason_code,
            'exp_mest_reason_name' => $request->exp_mest_reason_name,
            'is_depa' => $request->is_depa,
            'is_odd' => $request->is_odd,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
}