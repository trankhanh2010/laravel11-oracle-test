<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\DepositReq;
use Illuminate\Support\Facades\DB;

class DepositReqRepository
{
    protected $depositReq;
    public function __construct(DepositReq $depositReq)
    {
        $this->depositReq = $depositReq;
    }

    public function applyJoins()
    {
        return $this->depositReq
            ->select(
                'his_deposit_req.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_deposit_req.deposit_req_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_deposit_req.deposit_req_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_deposit_req.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_deposit_req.' . $key, $item);
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
        return $this->depositReq->find($id);
    }
    public function getByCode($code)
    {
        return $this->depositReq->where('deposit_req_code', $code)->first();
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->depositReq::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'deposit_req_code' => $request->deposit_req_code,
            'deposit_req_name' => $request->deposit_req_name,
        ]);
        return $data;
    }
    public function updateDepositId($data, $depositId){
        $data->update([
            'deposit_id' => $depositId
        ]);
        return $data;
    }
 
}