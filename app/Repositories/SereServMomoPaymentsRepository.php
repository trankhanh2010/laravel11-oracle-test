<?php 
namespace App\Repositories;

use App\Models\HIS\SereServMomoPayments;
use Illuminate\Support\Facades\DB;

class SereServMomoPaymentsRepository
{
    protected $sereServMomoPayments;
    public function __construct(SereServMomoPayments $sereServMomoPayments)
    {
        $this->sereServMomoPayments = $sereServMomoPayments;
    }

    public function applyJoins()
    {
        return $this->sereServMomoPayments
            ->select(
                'his_sere_serv_momo_payments.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_momo_payments.sere_serv_momo_payments_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_momo_payments.sere_serv_momo_payments_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv_momo_payments.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_sere_serv_momo_payments.' . $key, $item);
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
        return $this->sereServMomoPayments->find($id);
    }
    public function getByTreatmentMomoPaymentsId($id)
    {
        return $this->sereServMomoPayments->where('treatment_momo_payments_id', $id)->get();
    }
    public function create($data, $appCreator, $appModifier){
        $data = $this->sereServMomoPayments::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            // 'creator' => get_loginname_with_token($request->bearerToken(), $time),
            // 'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'sere_serv_id' =>$data['sere_serv_id'],
            'treatment_momo_payments_id' =>$data['treatment_momo_payments_id'],
        ]);
        return $data;
    }

}