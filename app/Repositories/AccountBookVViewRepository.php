<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\AccountBookVView;
use Illuminate\Support\Facades\DB;

class AccountBookVViewRepository
{
    protected $accountBookVView;
    public function __construct(AccountBookVView $accountBookVView)
    {
        $this->accountBookVView = $accountBookVView;
    }

    public function applyJoins()
    {
        return $this->accountBookVView
            ->select(
                'v_his_account_book.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_account_book.account_book_code'), 'like', '%'. $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('lower(v_his_account_book.account_book_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_account_book.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_account_book.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyIsForBillFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(DB::connection('oracle_his')->raw('v_his_account_book.is_for_bill'), $param);
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_account_book.is_for_bill'))
                ->orWhereNull(DB::connection('oracle_his')->raw('v_his_account_book.is_for_bill'), $param);
            }
        }
        return $query;
    }
    public function applyIsForRepayFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(DB::connection('oracle_his')->raw('v_his_account_book.is_for_repay'), $param);
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_account_book.is_for_repay'))
                ->orWhereNull(DB::connection('oracle_his')->raw('v_his_account_book.is_for_repay'), $param);
            }
        }
        return $query;
    }
    public function applyIsForDepositFilter($query, $param)
    {
        if ($param !== null) {
            if($param == 1){
                $query->where(DB::connection('oracle_his')->raw('v_his_account_book.is_for_deposit'), $param);
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_account_book.is_for_deposit'))
                ->orWhereNull(DB::connection('oracle_his')->raw('v_his_account_book.is_for_deposit'), $param);
            }
        }
        return $query;
    }
    public function applyDebateIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_account_book.debate_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_account_book.' . $key, $item);
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
        return $this->accountBookVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->accountBookVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'account_book_v_view_code' => $request->account_book_v_view_code,
    //         'account_book_v_view_name' => $request->account_book_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'account_book_v_view_code' => $request->account_book_v_view_code,
    //         'account_book_v_view_name' => $request->account_book_v_view_name,
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
            $data = $this->applyJoins()->where('v_his_account_book.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_account_book.id');
            $maxId = $this->applyJoins()->max('v_his_account_book.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('account_book_v_view', 'v_his_account_book', $startId, $endId, $batchSize);
            }
        }
    }
}