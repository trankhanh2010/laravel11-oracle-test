<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\UserAccountBookVView;
use Illuminate\Support\Facades\DB;

class UserAccountBookVViewRepository
{
    protected $userUserAccountBookVView;
    public function __construct(UserAccountBookVView $userUserAccountBookVView)
    {
        $this->userUserAccountBookVView = $userUserAccountBookVView;
    }

    public function applyJoins()
    {
        return $this->userUserAccountBookVView
            ->select(
                '*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('account_book_code'), 'like', '%'. $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('lower(account_book_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyIsForBillFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(('is_for_bill'), $param);
            }else{
                $query->whereNull(('is_for_bill'))
                ->orWhereNull(('is_for_bill'), $param);
            }
        }
        return $query;
    }
    public function applyIsForRepayFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(('is_for_repay'), $param);
            }else{
                $query->whereNull(('is_for_repay'))
                ->orWhereNull(('is_for_repay'), $param);
            }
        }
        return $query;
    }
    public function applyIsForDepositFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(('is_for_deposit'), $param);
            }else{
                $query->whereNull(('is_for_deposit'))
                ->orWhereNull(('is_for_deposit'), $param);
            }
        }
        return $query;
    }
    public function applyDebateIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(('debate_id'), $id);
        }
        return $query;
    }
    public function applyLoginnameFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('loginname'), $param);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('is_active'), 1)
            ->where(('account_book_is_active'), 1);
            if($param == 'tamUng'){
                $query->where(('is_for_deposit'), 1);
            }
            if($param == 'hoanUng'){
                $query->where(('is_for_repay'), 1);
            }
            if($param == 'thanhToan'){
                $query->where(('is_for_bill'), 1);
            }
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('' . $key, $item);
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
        return $this->userUserAccountBookVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->userUserAccountBookVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'user_account_book_v_view_code' => $request->user_account_book_v_view_code,
    //         'user_account_book_v_view_name' => $request->user_account_book_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'user_account_book_v_view_code' => $request->user_account_book_v_view_code,
    //         'user_account_book_v_view_name' => $request->user_account_book_v_view_name,
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
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('user_account_book_v_view', 'v_his_account_book', $startId, $endId, $batchSize);
            }
        }
    }
}