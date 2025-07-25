<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\BoVatTuDetailVView;
use Illuminate\Support\Facades\DB;

class BoVatTuDetailVViewRepository
{
    protected $boVatTuDetailVView;
    public function __construct(BoVatTuDetailVView $boVatTuDetailVView)
    {
        $this->boVatTuDetailVView = $boVatTuDetailVView;
    }

    public function applyJoins()
    {
        return $this->boVatTuDetailVView
            ->select(
                'xa_v_his_mau_don_th_tt_detail.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.mau_don_th_tt_detail_code'), 'like', '%'. $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('lower(xa_v_his_mau_don_th_tt_detail.mau_don_th_tt_detail_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyIsForBillFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_bill'), $param);
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_bill'))
                ->orWhereNull(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_bill'), $param);
            }
        }
        return $query;
    }
    public function applyIsForRepayFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_repay'), $param);
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_repay'))
                ->orWhereNull(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_repay'), $param);
            }
        }
        return $query;
    }
    public function applyIsForDepositFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_deposit'), $param);
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_deposit'))
                ->orWhereNull(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.is_for_deposit'), $param);
            }
        }
        return $query;
    }
    public function applyDebateIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_mau_don_th_tt_detail.debate_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('xa_v_his_mau_don_th_tt_detail.' . $key, $item);
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
        return $this->boVatTuDetailVView->find($id);
    }
    public function getByEquipmentSetId($equipmentSetId)
    {
        return $this->boVatTuDetailVView
        ->select([
            'xa_v_his_bo_vat_tu_detail.*',
        ])
        ->where('xa_v_his_bo_vat_tu_detail.equipment_set_id', $equipmentSetId)
        ->where('xa_v_his_bo_vat_tu_detail.is_active', 1)
        ->where('xa_v_his_bo_vat_tu_detail.is_delete', 0)
        ->get();
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->boVatTuDetailVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'mau_don_th_tt_detail_v_view_code' => $request->mau_don_th_tt_detail_v_view_code,
    //         'mau_don_th_tt_detail_v_view_name' => $request->mau_don_th_tt_detail_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'mau_don_th_tt_detail_v_view_code' => $request->mau_don_th_tt_detail_v_view_code,
    //         'mau_don_th_tt_detail_v_view_name' => $request->mau_don_th_tt_detail_v_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('xa_v_his_mau_don_th_tt_detail.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('xa_v_his_mau_don_th_tt_detail.id');
            $maxId = $this->applyJoins()->max('xa_v_his_mau_don_th_tt_detail.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('mau_don_th_tt_detail_v_view', 'xa_v_his_mau_don_th_tt_detail', $startId, $endId, $batchSize);
            }
        }
    }
}